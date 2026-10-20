const initialized = new WeakSet();
function initialize() {
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
            select.replaceChildren(...options.filter(option => !option.value || option.value === selected || option.textContent.toLocaleLowerCase('es').includes(term)).map(option => option.cloneNode(true)));
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
        const update = () => {
            const lot = form.elements.id_lote.selectedOptions[0];
            form.querySelector('[data-lot-summary]').textContent = lot?.dataset.info || 'Selecciona un lote para revisar sus datos de ingreso.';
            const values = [lot?.value ? lot.textContent : '', form.elements.protocolo.value.trim(), form.elements.metodo?.value, form.elements.tamano_muestra.value];
            form.querySelector('[data-sampling-summary]').textContent = values.every(Boolean) ? `${values[0]} · Protocolo: ${values[1]} · Método: ${values[2]} · ${values[3]} frutos identificados.` : 'Completa lote, protocolo, método y cantidad para revisar la muestra.';
            const population = Number(form.elements.poblacion_estimada.value);
            form.elements.tamano_muestra.setCustomValidity(population > 0 && Number(form.elements.tamano_muestra.value) > population ? 'La muestra no puede superar la población estimada.' : '');
        };
        form.addEventListener('input', update); form.addEventListener('change', update); update();
    });
}
document.addEventListener('livewire:navigated', initialize);
document.addEventListener('DOMContentLoaded', initialize);
if (document.readyState !== 'loading') initialize();

document.addEventListener('workspace:saved', event => {
    if (event.detail?.module === 'recepciones' && document.querySelector('[data-lot-workspace]')) window.Livewire?.navigate(location.href);
});
