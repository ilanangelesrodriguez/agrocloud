const initialized = new WeakSet();
function initialize() {
    const expanded = document.getElementById(location.hash.slice(1));
    if (expanded instanceof HTMLDetailsElement) expanded.open = true;
    document.querySelectorAll('[data-stage-tabs]').forEach(root => {
        if (initialized.has(root)) return;
        initialized.add(root);
        const links = [...root.querySelectorAll('[data-stage-tab]')];
        const panels = [...root.querySelectorAll('[data-stage-panel]')];
        root.querySelector('nav')?.setAttribute('role', 'tablist');
        const activate = (id, focus = false) => {
            links.forEach(link => {
                const selected = link.dataset.stageTab === id;
                link.setAttribute('role', 'tab'); link.setAttribute('aria-selected', String(selected));
                link.setAttribute('aria-controls', link.dataset.stageTab); link.tabIndex = selected ? 0 : -1;
                link.classList.toggle('bg-brand-50', selected); link.classList.toggle('dark:bg-brand-950', selected);
                link.id ||= `tab-${link.dataset.stageTab}`;
                if (selected && focus) link.focus();
            });
            panels.forEach(panel => { panel.hidden = panel.id !== id; panel.setAttribute('role', 'tabpanel'); panel.setAttribute('aria-labelledby', `tab-${panel.id}`); });
        };
        links.forEach((link, index) => {
            link.addEventListener('click', event => { event.preventDefault(); history.replaceState(history.state, '', link.hash); activate(link.dataset.stageTab); });
            link.addEventListener('keydown', event => {
                const next = event.key === 'ArrowRight' ? (index + 1) % links.length : event.key === 'ArrowLeft' ? (index + links.length - 1) % links.length : event.key === 'Home' ? 0 : event.key === 'End' ? links.length - 1 : null;
                if (next !== null) { event.preventDefault(); history.replaceState(history.state, '', links[next].hash); activate(links[next].dataset.stageTab, true); }
            });
        });
        if (links.length) activate(links.find(link => link.hash === location.hash)?.dataset.stageTab || links[0].dataset.stageTab);
    });
    document.querySelectorAll('[data-option-search]').forEach(input => {
        if (initialized.has(input)) return;
        const select = document.getElementById(input.dataset.optionSearch);
        if (!select) return;
        initialized.add(input);
        const options = [...select.options].map(option => option.cloneNode(true));
        input.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); select.focus(); } });
        input.addEventListener('input', () => {
            const selected = select.value;
            const term = input.value.trim().toLocaleLowerCase('es');
            select.replaceChildren(...options.filter(option => !option.value || option.value === selected || `${option.textContent} ${option.dataset.info || ''}`.toLocaleLowerCase('es').includes(term)).map(option => option.cloneNode(true)));
            select.value = selected;
        });
    });
    document.querySelectorAll('[data-lot-create-form]').forEach(form => {
        if (initialized.has(form)) return;
        initialized.add(form);
        const update = () => {
            const producer = form.elements.id_productor.selectedOptions[0];
            const values = [form.elements.codigo_lote.value.trim(), producer?.value ? producer.textContent.trim() : '', form.elements.producto.value, form.elements.peso_kg.value, form.elements.unidad_medida.value];
            form.querySelector('[data-lot-create-summary]').textContent = values.every(Boolean) ? `${values[0]} · ${values[1]} · ${values[2]} ${form.elements.variedad?.value || ''} · Peso declarado: ${values[3]} ${values[4]} · Pendiente de recepción.` : 'Completa código, productor, producto, peso y unidad para revisar el lote.';
        };
        form.addEventListener('input', update); form.addEventListener('change', update); update();
    });
    document.querySelectorAll('[data-sampling-form]').forEach(form => {
        if (initialized.has(form)) return;
        initialized.add(form);
        const procedure = form.querySelector('[data-sampling-procedure]');
        const countInput = form.elements.tamano_muestra;
        const defaultMax = countInput.max;
        let selected = null;
        const mode = () => form.querySelector('input[name="sampling_mode"]:checked')?.value || (procedure?.selectedOptions[0]?.dataset.procedure ? 'authorized' : 'manual');
        const applyProcedure = (replaceCount = false) => {
            const manual = mode() === 'manual';
            const lot = form.elements.id_lote.selectedOptions[0];
            const product = lot?.dataset.product;
            const options = [...(procedure?.options || [])].filter(option => {
                if (!option.dataset.procedure) return false;
                const data = JSON.parse(option.dataset.procedure);
                const applicable = !data.producto || data.producto === product;
                option.hidden = !applicable; option.disabled = !applicable;
                return applicable;
            });
            if (!manual && product && procedure?.options) {
                if (procedure.selectedOptions[0]?.disabled) {
                    procedure.value = '';
                    const notice = form.querySelector('[data-procedure-change-notice]');
                    if (notice) { notice.hidden = false; notice.textContent = 'El procedimiento anterior no corresponde al producto del nuevo lote. Revisa el procedimiento aplicable y confirma sus instrucciones.'; }
                    if (form.elements.confirmar_instrucciones) form.elements.confirmar_instrucciones.checked = false;
                }
                if (!procedure.value && options.length === 1) procedure.value = options[0].value;
            }
            if (procedure) { procedure.disabled = manual; procedure.required = !manual; }
            const raw = manual ? null : procedure?.selectedOptions[0]?.dataset.procedure;
            selected = raw ? JSON.parse(raw) : null;
            form.querySelector('[data-authorized-fields]')?.toggleAttribute('hidden', manual);
            form.querySelector('[data-manual-fields]')?.toggleAttribute('hidden', !manual);
            ['protocolo', 'criterio_muestra', 'seleccion_frutos'].forEach(name => {
                form.elements[name].readOnly = Boolean(selected);
                form.elements[name].required = manual;
                if (selected) form.elements[name].value = name === 'protocolo' ? `${selected.nombre} · ${selected.version}` : selected[name];
            });
            if (form.elements.motivo_excepcion) form.elements.motivo_excepcion.required = manual;
            form.elements.metodo.disabled = !manual;
            form.elements.metodo.required = manual;
            countInput.max = selected?.max_frutos || defaultMax;
            if (selected) {
                form.elements.metodo.value = selected.metodo;
                if (selected.tamano_recomendado && (replaceCount || !countInput.value)) countInput.value = selected.tamano_recomendado;
            }
            const instructions = form.querySelector('[data-procedure-instructions]');
            if (instructions) instructions.textContent = selected ? `${selected.nombre} · ${selected.version}\nMétodo: ${selected.metodo}\nCantidad recomendada: ${selected.tamano_recomendado || 'Según criterio documentado'} · Máximo: ${selected.max_frutos} frutos\nCriterio: ${selected.criterio_muestra}\nCómo tomar los frutos: ${selected.seleccion_frutos}` : 'No hay un procedimiento seleccionado. Selecciona uno aplicable o consulta a Administración. La excepción manual requiere justificación y debe estar permitida por tu empresa.';
        };
        const update = () => {
            const count = Number(countInput.value);
            const workload = form.querySelector('[data-sampling-workload]');
            if (workload) workload.textContent = count > 0 ? `Se identificarán ${count} frutos. Si el procedimiento requiere una fotografía por fruto, serán al menos ${count} fotografías y ${count} revisiones; comprueba el requisito del procedimiento.` : 'Indica la cantidad para estimar el trabajo de identificación y revisión.';
            const confirmation = form.elements.confirmar_carga;
            const large = count > Number(form.dataset.samplingWarning);
            if (confirmation) { confirmation.required = large; if (!large) confirmation.checked = false; }
            form.querySelector('[data-sampling-load-confirmation]')?.toggleAttribute('hidden', !large);
            const lot = form.elements.id_lote.selectedOptions[0];
            form.querySelector('[data-lot-summary]').textContent = lot?.dataset.info || 'Selecciona un lote para revisar sus datos de ingreso.';
            const values = [lot?.value ? lot.textContent : '', form.elements.protocolo.value.trim(), selected?.metodo || form.elements.metodo?.value, countInput.value];
            form.querySelector('[data-sampling-summary]').textContent = values.every(Boolean) ? `Lote: ${values[0]}\nProcedimiento: ${values[1]}\nMétodo: ${values[2]}\n${values[3]} frutos que se identificarán.\nModalidad: ${mode() === 'manual' ? 'Excepción manual documentada' : 'Procedimiento autorizado'}` : 'Completa lote, procedimiento, método y cantidad para revisar la muestra.';
            const summary = form.querySelector('[data-sampling-summary]');
            if (values.every(Boolean)) {
                const population = form.elements.poblacion_estimada;
                summary.textContent += `\nResponsable: ${form.dataset.samplingResponsible || 'Usuario autenticado'}\nEstimación del lote: ${mode() === 'manual' || selected?.requiere_poblacion ? (population.value || 'Sin registrar') : 'No requerida'}\nFuente: ${mode() === 'manual' || selected?.requiere_poblacion ? (form.elements.fuente_poblacion?.value || 'Sin registrar') : 'No requerida'}`;
                if (selected?.tamano_recomendado && count !== Number(selected.tamano_recomendado)) summary.textContent += `\nMotivo de desviación: ${form.elements.motivo_desviacion?.value || 'Pendiente de justificar'}`;
                if (mode() === 'manual') summary.textContent += `\nMotivo de excepción: ${form.elements.motivo_excepcion?.value || 'Pendiente de justificar'}`;
            }
            const guidance = form.querySelector('[data-sampling-guidance]');
            if (guidance) guidance.textContent = selected ? `Este procedimiento ${selected.tamano_recomendado ? 'recomienda inspeccionar ' + selected.tamano_recomendado + ' frutos' : 'define la cantidad mediante su criterio documentado'}. El máximo permitido es ${selected.max_frutos} frutos. Si cambias la cantidad recomendada, justifica el motivo. Estimación del lote: ${selected.requiere_poblacion ? 'obligatoria' : 'no requerida'}.` : 'Documenta la cantidad según el criterio técnico y respeta el máximo permitido.';
            const deviation = Boolean(selected?.tamano_recomendado && count > 0 && count !== selected.tamano_recomendado);
            form.querySelector('[data-sampling-deviation]')?.toggleAttribute('hidden', !deviation);
            if (form.elements.motivo_desviacion) form.elements.motivo_desviacion.required = deviation;
            const populationSection = form.querySelector('[data-population-fields]');
            if (populationSection) { populationSection.hidden = mode() !== 'manual' && !selected?.requiere_poblacion; if (selected?.requiere_poblacion) populationSection.open = true; }
            const populationInput = form.elements.poblacion_estimada;
            populationInput.disabled = mode() !== 'manual' && !selected?.requiere_poblacion;
            populationInput.required = Boolean(selected?.requiere_poblacion);
            const population = populationInput.disabled ? 0 : Number(populationInput.value);
            if (form.elements.fuente_poblacion) { form.elements.fuente_poblacion.disabled = populationInput.disabled; form.elements.fuente_poblacion.required = population > 0; }
            if (form.elements.confirmar_instrucciones) { form.elements.confirmar_instrucciones.disabled = mode() === 'manual'; form.elements.confirmar_instrucciones.required = mode() !== 'manual'; }
            countInput.setCustomValidity(population > 0 && count > population ? 'La muestra no puede superar la población estimada.' : '');
        };
        procedure?.addEventListener('change', () => { applyProcedure(true); update(); });
        form.addEventListener('input', update);
        form.addEventListener('change', event => {
            if (event?.target?.name === 'sampling_mode' || event?.target?.name === 'id_lote') applyProcedure();
            update();
        });
        applyProcedure(); update();
        const panels = [...(form.querySelectorAll?.('[data-sampling-step]') || [])];
        if (panels.length) {
            let step = 0;
            const nav = form.querySelector('[data-sampling-navigation]');
            const previous = form.querySelector('[data-sampling-previous]');
            const next = form.querySelector('[data-sampling-next]');
            const submit = form.querySelector('[data-sampling-submit]');
            const links = [...form.querySelectorAll('[data-sampling-go]')];
            const showStep = index => {
                step = index;
                panels.forEach((panel, i) => { panel.hidden = i !== step; });
                links.forEach((link, i) => { link.setAttribute('aria-current', i === step ? 'step' : 'false'); link.classList.toggle('bg-brand-50', i === step); });
                previous.hidden = step === 0; next.hidden = step === panels.length - 1; submit.hidden = !next.hidden;
            };
            const validUntil = index => {
                for (let i = 0; i < index; i++) {
                    const invalid = [...panels[i].querySelectorAll('input, select, textarea')].find(input => !input.disabled && !input.checkValidity());
                    if (invalid) { showStep(i); invalid.reportValidity(); invalid.focus(); return false; }
                }
                return true;
            };
            nav.hidden = false;
            next.addEventListener('click', () => { if (validUntil(step + 1)) showStep(step + 1); });
            previous.addEventListener('click', () => showStep(Math.max(0, step - 1)));
            links.forEach(link => link.addEventListener('click', () => { const index = Number(link.dataset.samplingGo); if (index < step || validUntil(index)) showStep(index); }));
            const reveal = input => { const panel = input?.closest('[data-sampling-step]'); if (panel) showStep(Number(panel.dataset.samplingStep)); };
            form.addEventListener('invalid', event => reveal(event.target), true);
            form.addEventListener('form:errors', event => reveal(event.detail.first));
            form.addEventListener('submit', event => {
                if (step !== panels.length - 1) { event.preventDefault(); event.stopImmediatePropagation(); if (validUntil(panels.length - 1)) showStep(panels.length - 1); }
            });
            showStep(0);
            reveal(form.querySelector('[aria-invalid="true"]'));
        }
    });
}
document.addEventListener('livewire:navigated', initialize);
document.addEventListener('DOMContentLoaded', initialize);
if (document.readyState !== 'loading') initialize();

document.addEventListener('workspace:saved', event => {
    if (event.detail?.module === 'incidencias' || (event.detail?.module === 'recepciones' && document.querySelector('[data-lot-workspace]'))) window.Livewire?.navigate(location.href);
});
