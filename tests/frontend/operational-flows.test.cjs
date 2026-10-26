const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');

test('un procedimiento completa instrucciones y límite y exige confirmar muestras grandes', () => {
    const handlers = {};
    const events = {};
    const procedureEvents = {};
    const output = {};
    const nodes = Object.fromEntries(['data-lot-summary', 'data-sampling-summary', 'data-sampling-workload'].map(key => [key, {}]));
    const field = value => ({ value, setCustomValidity(message) { this.error = message; } });
    const procedure = { selectedOptions: [{ dataset: {} }], addEventListener: (name, fn) => { procedureEvents[name] = fn; } };
    const form = {
        dataset: { samplingWarning: '500' },
        elements: {
            id_lote: { selectedOptions: [{ value: '1', textContent: 'Lote de prueba', dataset: { info: 'Recepción confirmada' } }] },
            protocolo: field(''), metodo: field(''), criterio_muestra: field(''), seleccion_frutos: field(''),
            tamano_muestra: { ...field(''), max: '100000' }, poblacion_estimada: field('1000'), confirmar_carga: {},
        },
        querySelector: selector => selector === '[data-sampling-procedure]' ? procedure : nodes[selector.slice(1, -1)],
        addEventListener: (name, fn) => { events[name] = fn; },
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../resources/js/operational-workspace.js'), 'utf8'), {
        location: { hash: '' }, HTMLDetailsElement: class {},
        document: { readyState: 'loading', getElementById: () => null, querySelectorAll: selector => selector === '[data-sampling-form]' ? [form] : [], addEventListener: (name, fn) => { handlers[name] = fn; } },
        window: {},
    });
    handlers.DOMContentLoaded();
    procedure.selectedOptions[0].dataset.procedure = JSON.stringify({ nombre: 'Procedimiento autorizado', version: '2', metodo: 'Distribuido', criterio_muestra: 'Según protocolo', seleccion_frutos: 'Sectores del lote', max_frutos: 800, tamano_recomendado: 600, requiere_poblacion: true });
    procedureEvents.change(); events.change();
    assert.equal(form.elements.protocolo.value, 'Procedimiento autorizado · 2');
    assert.equal(form.elements.criterio_muestra.readOnly, true);
    assert.equal(form.elements.tamano_muestra.max, 800);
    assert.equal(form.elements.confirmar_carga.required, true);
    assert.match(nodes['data-sampling-workload'].textContent, /600 fotografías/);
    form.elements.tamano_muestra.value = '1001'; events.input();
    assert.match(form.elements.tamano_muestra.error, /población estimada/);
    delete procedure.selectedOptions[0].dataset.procedure;
    procedureEvents.change(); events.change();
    assert.equal(form.elements.criterio_muestra.readOnly, false);
    assert.equal(form.elements.tamano_muestra.max, '100000');
    form.elements.tamano_muestra.value = '10'; events.input();
    assert.equal(form.elements.confirmar_carga.required, false);
    output.summary = nodes['data-sampling-summary'].textContent;
    assert.match(output.summary, /10 frutos que se identificarán/);
    assert.match(output.summary, /Responsable:/);
});

test('confirmar una observación documental no anuncia una nueva inspección', () => {
    const handlers = {};
    const events = {};
    const decision = { value: 'OBSERVADO' };
    const form = {
        dataset: { qualityLot: 'LOTE-1', qualitySupervisor: 'Supervisora' },
        elements: { tipo_observacion: { value: 'DOCUMENTACION', parentElement: {} }, protocolo_calidad: { value: 'Calidad v1' }, observaciones: { value: 'Documento pendiente' }, revision_independiente: { checked: true } },
        querySelector: selector => selector.includes('input[name="decision"]') ? decision : selector.includes('select[name="motivo"]') ? null : {},
        addEventListener: (name, fn) => { events[name] = fn; },
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../resources/js/quality-decision.js'), 'utf8'), {
        document: { readyState: 'loading', querySelectorAll: () => [form], addEventListener: (name, fn) => { handlers[name] = fn; } },
    });
    handlers.DOMContentLoaded();
    assert.match(form.dataset.confirm, /no se abrirá una nueva inspección/);
    form.elements.tipo_observacion.value = 'REINSPECCION'; events.change();
    assert.match(form.dataset.confirm, /Se abrirá una reinspección/);
    decision.value = 'APROBADO'; events.change();
    assert.equal(form.elements.tipo_observacion.disabled, true);
    assert.match(form.dataset.confirm, /liberación/);
});


test('buscar lotes encuentra el productor incluido en los datos de ingreso', () => {
    const handlers = {}, events = {};
    const option = (value, textContent, info = '') => ({ value, textContent, dataset: { info }, cloneNode() { return option(this.value, this.textContent, this.dataset.info); } });
    const select = {
        value: '', options: [option('', 'Selecciona un lote'), option('1', 'LOTE-1 Mango', 'Productor Ana Torres'), option('2', 'LOTE-2 Palta', 'Productor Luis')],
        replaceChildren(...options) { this.options = options; },
    };
    const input = { dataset: { optionSearch: 'id_lote' }, value: 'ana torres', addEventListener(name, fn) { events[name] = fn; } };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../resources/js/operational-workspace.js'), 'utf8'), {
        location: { hash: '' }, HTMLDetailsElement: class {}, window: {},
        document: { readyState: 'loading', getElementById: id => id === 'id_lote' ? select : null, querySelectorAll: selector => selector === '[data-option-search]' ? [input] : [], addEventListener(name, fn) { handlers[name] = fn; } },
    });
    handlers.DOMContentLoaded(); events.input();
    assert.deepEqual(select.options.map(item => item.value), ['', '1']);
    input.value = ''; events.input();
    assert.equal(select.options.length, 3);
});
