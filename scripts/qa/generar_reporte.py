"""Consolida evidencias JUnit reales, sin convertir ausencias en aprobaciones."""
import argparse
import csv
import html
import json
import math
import os
from pathlib import Path
import re
from datetime import datetime, timezone
import xml.etree.ElementTree as ET


def redact(value):
    value = str(value or "")
    value = re.sub(r"(?i)(postgres(?:ql)?|pgsql)://[^\s<>]+", "[conexión protegida]", value)
    value = re.sub(r"(?i)(password|passwd|pwd)\s*[=:]\s*[^\s;]+", r"\1=[protegido]", value)
    for key in ("DB_URL", "DB_PASSWORD", "NEON_TEST_DATABASE_URL"):
        secret = os.environ.get(key, "")
        if len(secret) > 3:
            value = value.replace(secret, "[protegido]")
    return value


def consolidate(catalog, inputs):
    indexed = {case["test"]: case for case in catalog}
    results, warnings, seen = [], [], set()
    received = set()
    for path in inputs:
        try:
            root = ET.parse(path).getroot()
        except (ET.ParseError, OSError) as error:
            warnings.append(f"Evidencia ilegible: {path.name} ({type(error).__name__}).")
            continue
        for test in root.iter("testcase"):
            name = test.get("name", "Sin nombre")
            classname = test.get("class") or test.get("classname", "").replace(".", "\\")
            base_name = name.split(" with data set", 1)[0]
            key = f"{classname}::{base_name}" if classname else name
            signature = (key, name)
            if signature in received:
                warnings.append("Evidencia duplicada para " + redact(key))
            received.add(signature)
            case = indexed.get(key)
            # Node puede omitir classname o incluir el nombre del archivo.
            if not case:
                case = indexed.get(name)
            if case:
                seen.add(case["test"])
            state = "Aprobado"
            detail = []
            for tag, label in (("failure", "Fallido"), ("error", "Error"), ("skipped", "Omitido")):
                nodes = test.findall(tag)
                if nodes:
                    state = label
                    detail = [redact((node.get("message", "") + "\n" + "".join(node.itertext())).strip()) for node in nodes]
                    break
            try:
                duration = max(0, float(test.get("time", "0")))
                if not math.isfinite(duration):
                    raise ValueError("Duración no finita")
            except ValueError:
                duration = 0
                warnings.append(f"Duración inválida en {path.name}.")
            result = dict(case or {"id": "SIN-CATALOGO", "titulo": name, "modulo": "Sin clasificar", "categoria": "Sin clasificar", "test": key})
            result.update(estado=state, duracion=duration, detalle="\n".join(detail), evidencia=path.name)
            results.append(result)
            if not case:
                warnings.append("Se detectó una prueba sin ficha en el catálogo: " + redact(key))
    for case in catalog:
        if case["test"] not in seen:
            results.append(dict(case, estado="No ejecutado", duracion=0, detalle="No se recibió evidencia JUnit para este caso.", evidencia=""))
    return results, warnings


def generate(catalog_path, inputs, output, stages=None):
    catalog = json.loads(Path(catalog_path).read_text(encoding="utf-8"))["casos"]
    results, warnings = consolidate(catalog, inputs)
    counts = {state: sum(case["estado"] == state for case in results) for state in ("Aprobado", "Fallido", "Error", "Omitido", "No ejecutado")}
    executed = counts["Aprobado"] + counts["Fallido"] + counts["Error"]
    stages = stages or {}
    complete = not warnings and not counts["No ejecutado"] and not counts["Omitido"]
    passed = complete and executed > 0 and not counts["Fallido"] and not counts["Error"] and all(value == "success" for value in stages.values())
    context = {"commit": os.getenv("GITHUB_SHA", "local"), "ejecucion": os.getenv("GITHUB_RUN_ID", "local"), "intento": os.getenv("GITHUB_RUN_ATTEMPT", "1"), "fecha_utc": datetime.now(timezone.utc).isoformat()}
    repo = os.getenv("GITHUB_REPOSITORY", "")
    server = os.getenv("GITHUB_SERVER_URL", "https://github.com")
    context["url"] = f"{server}/{repo}/actions/runs/{context['ejecucion']}" if repo else ""
    summary = {"contexto": context, "etapas": stages, "resultado": "Aprobado" if passed else "Requiere revisión", "catalogados": len(catalog), "resultados": len(results), "ejecutados": executed, "estados": counts, "aprobacion_ejecutados": round(counts["Aprobado"] / executed * 100, 2) if executed else None, "duracion_acumulada_segundos": round(sum(case["duracion"] for case in results), 3), "advertencias": warnings, "casos": results}
    output = Path(output)
    output.mkdir(parents=True, exist_ok=True)
    (output / "resumen.json").write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8")
    columns = ["id", "titulo", "modulo", "categoria", "estado", "duracion", "evidencia", "test", "detalle"]
    with (output / "casos.csv").open("w", encoding="utf-8-sig", newline="") as file:
        writer = csv.DictWriter(file, fieldnames=columns, extrasaction="ignore")
        writer.writeheader()
        for case in results:
            # Evitar interpretar datos de errores como fórmulas al abrir el CSV.
            writer.writerow({key: ("'" + str(case.get(key, "")) if str(case.get(key, "")).startswith(("=", "+", "-", "@")) else case.get(key, "")) for key in columns})
    esc = lambda value: html.escape(redact(value), quote=True)
    rows = []
    for case in results:
        evidence = f'<a href="evidencias/{esc(case["evidencia"])}">JUnit</a>' if case["evidencia"] else "Sin evidencia"
        detail = f'<details><summary>Detalle técnico</summary><pre>{esc(case["detalle"])}</pre></details>' if case["detalle"] else ""
        ficha = f'<details><summary>Ficha y criterio de aceptación</summary><p><strong>Referencia:</strong> {esc(case.get("requerimiento", "Sin referencia"))} · <strong>Prioridad:</strong> {esc(case.get("prioridad", "Sin definir"))}</p><p>{esc(case.get("resultado_esperado", "Sin criterio catalogado"))}</p><small>{esc(case["test"])}</small></details>'
        rows.append(f'<tr data-module="{esc(case["modulo"])}" data-state="{esc(case["estado"])}"><td><strong>{esc(case["id"])}</strong><small>{esc(case["categoria"])}</small></td><td>{esc(case["titulo"])}<small>{esc(case["modulo"])}</small>{ficha}{detail}</td><td><span class="badge">{esc(case["estado"])}</span></td><td>{case["duracion"]:.3f} s</td><td>{evidence}</td></tr>')
    options = ''.join(f'<option>{esc(module)}</option>' for module in sorted({case["modulo"] for case in results}))
    states = ''.join(f'<option>{esc(state)}</option>' for state in counts)
    alerts = ''.join(f'<li>{esc(warning)}</li>' for warning in warnings)
    stage_html = ''.join(f'<li>{esc(name)}: <strong>{esc(state)}</strong></li>' for name, state in stages.items())
    approval = f'{summary["aprobacion_ejecutados"]}%' if executed else "Sin datos"
    page = """<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AgroCloud AI · Informe QA</title><style>
:root{font-family:system-ui,sans-serif;color:#172b22;background:#f3f7f4;color-scheme:light}*{box-sizing:border-box}body{margin:0}header{background:#124932;color:white;padding:40px max(5vw,20px)}main{max-width:1250px;margin:auto;padding:28px 20px}h1{font-size:clamp(1.8rem,4vw,2.7rem);margin:12px 0}header p{color:#d8f8e6;max-width:800px;line-height:1.7}.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px}.card,section{background:white;border:1px solid #dce6df;border-radius:16px;padding:22px;margin-bottom:20px}.value{display:block;font-size:30px;font-weight:700;margin-top:8px}small{display:block;color:#52665b;margin-top:6px;font-size:12px}a{color:#146f47}header a{color:#d8f8e6}.filters{display:flex;flex-wrap:wrap;gap:14px;margin:20px 0}label{display:flex;flex:1;min-width:170px;flex-direction:column;gap:7px;font-size:14px}input,select,button{padding:12px;border:1px solid #b7c8bd;border-radius:9px;font:inherit;background:white;color:inherit}button{cursor:pointer}button:focus-visible,a:focus-visible,input:focus-visible,select:focus-visible,summary:focus-visible{outline:3px solid #20ad6b;outline-offset:3px}.table{overflow:auto}table{border-collapse:collapse;width:100%;font-size:14px;text-align:left}th,td{padding:16px 12px;border-bottom:1px solid #e4ebe6;vertical-align:top}th{font-size:12px;color:#52665b}td:nth-child(2){min-width:260px}.badge{display:inline-block;padding:5px 10px;border-radius:20px;background:#edf2ef;white-space:nowrap}tr[data-state="Aprobado"] .badge{background:#d8f8e6;color:#124932}tr[data-state="Fallido"] .badge,tr[data-state="Error"] .badge{background:#fee2e2;color:#991b1b}pre{white-space:pre-wrap;overflow-wrap:anywhere;max-width:650px;background:#f5f7f6;padding:12px}summary{cursor:pointer;margin-top:10px}li{line-height:1.8}.meta{overflow-wrap:anywhere}footer{padding:24px;text-align:center;color:#52665b;font-size:12px}[hidden]{display:none!important}@media print{.filters{display:none}header{background:white;color:black}header p,header a{color:black}main{max-width:none}.table{overflow:visible}section{break-inside:auto}}
</style></head><body><header><p>AGROCLOUD AI · CONTROL DE CALIDAD DEL SOFTWARE</p><h1>Informe de calidad del software</h1><p>Resultados y evidencias de pruebas automatizadas. Los casos sin evidencia se muestran como no ejecutados; no se consideran aprobados.</p>@@META@@</header><main>@@CARDS@@<section><h2>Estado de la ejecución: @@RESULTADO@@</h2><ul>@@ETAPAS@@</ul><p>La tasa de aprobación utiliza solamente casos ejecutados. Omitidos y no ejecutados se informan por separado. La duración es acumulada por caso.</p><ul>@@ALERTAS@@</ul></section><section><h2>Catálogo de resultados</h2><p><a href="casos.csv">Descargar CSV</a> · <a href="resumen.json">Consultar JSON</a></p><div class="filters"><label>Buscar caso<input id="search" type="search" placeholder="ID o descripción"></label><label>Módulo<select id="module"><option value="">Todos los módulos</option>@@MODULOS@@</select></label><label>Resultado<select id="state"><option value="">Todos los resultados</option>@@ESTADOS@@</select></label><button id="clear" hidden>Limpiar filtros</button></div><p id="visible" role="status" aria-live="polite"></p><div class="table"><table><thead><tr><th>ID / tipo</th><th>Caso de prueba</th><th>Resultado</th><th>Duración</th><th>Evidencia</th></tr></thead><tbody>@@FILAS@@</tbody></table></div></section></main><footer>Informe generado desde JUnit y el catálogo de casos. Sin capturas ni resultados simulados.</footer><script>
const search=document.getElementById('search'),moduleFilter=document.getElementById('module'),state=document.getElementById('state'),clear=document.getElementById('clear');function filter(){let count=0;document.querySelectorAll('tbody tr').forEach(row=>{row.hidden=!(row.textContent.toLowerCase().includes(search.value.toLowerCase())&&(!moduleFilter.value||row.dataset.module===moduleFilter.value)&&(!state.value||row.dataset.state===state.value));if(!row.hidden)count++});document.getElementById('visible').textContent=count+' resultados visibles';clear.hidden=!(search.value||moduleFilter.value||state.value)}[search,moduleFilter,state].forEach(el=>el.addEventListener('input',filter));clear.addEventListener('click',()=>{search.value=moduleFilter.value=state.value='';filter();search.focus()});filter();
</script></body></html>"""
    cards = '<div class="cards">' + ''.join(f'<div class="card">{esc(label)}<span class="value">{esc(value)}</span></div>' for label, value in (("Casos catalogados", len(catalog)), ("Ejecutados", executed), ("Aprobación de ejecutados", approval), ("Fallidos / errores", counts["Fallido"] + counts["Error"]), ("Omitidos", counts["Omitido"]), ("No ejecutados", counts["No ejecutado"]))) + '</div>'
    meta = f'<p class="meta">Commit: {esc(context["commit"])} · Ejecución: {esc(context["ejecucion"])} · Intento: {esc(context["intento"])}<br>{esc(context["fecha_utc"])}</p>'
    if context["url"]:
        meta += f'<a href="{esc(context["url"])}">Ver ejecución en GitHub Actions</a>'
    for key, value in {"META": meta, "CARDS": cards, "RESULTADO": esc(summary["resultado"]), "ETAPAS": stage_html or '<li>Ejecución local; sin estado de jobs de CI.</li>', "ALERTAS": alerts, "MODULOS": options, "ESTADOS": states, "FILAS": ''.join(rows)}.items():
        page = page.replace('@@' + key + '@@', value)
    (output / 'index.html').write_text(page, encoding='utf-8')
    markdown = f"# AgroCloud AI · Informe QA\n\nResultado: **{summary['resultado']}**\n\n| Indicador | Valor |\n|---|---:|\n| Casos catalogados | {len(catalog)} |\n| Ejecutados | {executed} |\n| Aprobados | {counts['Aprobado']} |\n| Fallidos / errores | {counts['Fallido'] + counts['Error']} |\n| Omitidos | {counts['Omitido']} |\n| No ejecutados | {counts['No ejecutado']} |\n\nDescarga el artefacto **agrocloud-informe-qa**, descomprímelo y abre `index.html`.\n"
    (output / 'resumen.md').write_text(markdown, encoding='utf-8')
    if os.getenv('GITHUB_STEP_SUMMARY'):
        with open(os.environ['GITHUB_STEP_SUMMARY'], 'a', encoding='utf-8') as file:
            file.write(markdown)
    return summary


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--catalogo', default='scripts/qa/catalogo_casos.json')
    parser.add_argument('--entrada', default='reports/evidencias')
    parser.add_argument('--salida', default='reports')
    parser.add_argument('--etapas', default='{}')
    args = parser.parse_args()
    summary = generate(args.catalogo, sorted(Path(args.entrada).glob('*.xml')), args.salida, json.loads(args.etapas))
    print(f"Informe generado: {summary['resultado']}; {summary['ejecutados']} casos ejecutados.")
