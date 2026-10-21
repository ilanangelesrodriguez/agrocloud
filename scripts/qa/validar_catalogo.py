"""Verifica que todos los métodos reales tengan una ficha y un ID único."""
import json
from pathlib import Path
import re


def validate(root=Path('.')):
    catalog = json.loads((root / 'scripts/qa/catalogo_casos.json').read_text(encoding='utf-8'))['casos']
    actual = set()
    for path in (root / 'tests').rglob('*Test.php'):
        source = path.read_text(encoding='utf-8')
        namespace = re.search(r'namespace ([^;]+);', source)[1]
        classname = re.search(r'class (\w+) extends', source)[1]
        for method in re.findall(r'public function (test_\w+)\(', source):
            actual.add(namespace + '\\' + classname + '::' + method)
    for path in (root / 'tests/frontend').glob('*.test.cjs'):
        actual.update(re.findall(r"\btest\('([^']+)'", path.read_text(encoding='utf-8')))
    listed = {case['test'] for case in catalog}
    ids = {case['id'] for case in catalog}
    if actual != listed or len(listed) != len(catalog) or len(ids) != len(catalog):
        raise ValueError(f'Catálogo desactualizado. Sin ficha: {sorted(actual-listed)}; inexistentes: {sorted(listed-actual)}; revisar IDs duplicados.')
    for case in catalog:
        for field in ['id', 'requerimiento', 'titulo', 'modulo', 'categoria', 'prioridad', 'objetivo', 'precondiciones', 'pasos', 'resultado_esperado', 'evidencia']:
            if not case.get(field):
                raise ValueError(f'Ficha incompleta: {case["id"]}, campo {field}.')
        if not (root / case['archivo']).is_file():
            raise ValueError(f'Archivo no encontrado para {case["id"]}.')
    return len(catalog)


if __name__ == '__main__':
    print(f'Catálogo verificado: {validate()} casos con identificadores únicos.')
