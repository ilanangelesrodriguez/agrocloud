const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../resources/js/ruc-lookup.js'), 'utf8').replace(/^import .*;\r?\n/gm, '');

function setup(fetch) {
    const handlers = {};
    const status = { textContent: '' };
    const label = { textContent: '' };
    const apply = { hidden: true, matches: () => true, closest: () => panel };
    const query = { disabled: false, matches: () => false, closest: () => panel, setAttribute() {}, removeAttribute() {} };
    const input = { value: '20330791412', matches: () => true, closest: () => form, focus() {} };
    const name = { value: 'Nombre declarado', dispatchEvent: () => {} };
    const form = {
        querySelector: (selector) => selector === '[name="ruc"]' ? input : name,
        querySelectorAll: () => [panel],
    };
    const panel = {
        isConnected: true,
        dataset: { endpoint: '/consultas/ruc/productores', nameField: 'nombre_razon_social' },
        closest: () => form,
        querySelector: (selector) => ({ '[data-ruc-status]': status, '[data-ruc-label]': label, '[data-ruc-query]': query, '[data-ruc-apply]': apply })[selector],
    };
    vm.runInNewContext(source, {
        document: { addEventListener: (type, handler) => { handlers[type] = handler; }, querySelector: () => ({ content: 'csrf-test' }) },
        showFieldError: (input, message) => { input.error = message; },
        clearFieldError: (input) => { delete input.error; },
        fetch, location: { origin: 'https://agro.test' }, URL, AbortController, setTimeout, clearTimeout, Event,
    });
    return { input, name, status, apply, query, panel,
        click: (button = query) => handlers.click({ target: { closest: () => button } }),
        changeRuc: (value) => { input.value = value; handlers.input({ target: input }); },
    };
}

test('consulta muestra datos sin sobrescribirlos y permite aplicar explícitamente la razón social', async () => {
    let called;
    const ui = setup(async (url) => {
        called = url;
        return { ok: true, json: async () => ({ ruc: '20330791412', razon_social: 'Agrícola SAC', estado: 'ACTIVO', condicion: 'HABIDO', mensaje: 'Revisa los datos.' }) };
    });
    await ui.click();
    assert.equal(called.searchParams.get('ruc'), '20330791412');
    assert.equal(ui.name.value, 'Nombre declarado');
    assert.match(ui.status.textContent, /ACTIVO/);
    assert.equal(ui.apply.hidden, false);
    await ui.click(ui.apply);
    assert.equal(ui.name.value, 'Agrícola SAC');
    assert.equal(ui.apply.hidden, true);
});

test('descarta una respuesta cuando el usuario cambia el RUC durante la consulta', async () => {
    let resolve;
    const ui = setup(() => new Promise((done) => { resolve = done; }));
    const pending = ui.click();
    ui.changeRuc('20100070970');
    resolve({ ok: true, json: async () => ({ ruc: '20330791412', razon_social: 'Otra empresa', mensaje: 'Respuesta vieja' }) });
    await pending;
    assert.equal(ui.apply.hidden, true);
    assert.doesNotMatch(ui.status.textContent, /Respuesta vieja/);
    assert.equal(ui.query.disabled, false);
});

test('un RUC incompleto no consulta y un error de red permite continuar', async () => {
    let calls = 0;
    const ui = setup(async () => { calls++; throw new Error('offline'); });
    ui.changeRuc('123');
    await ui.click();
    assert.equal(calls, 0);
    assert.match(ui.status.textContent, /11 dígitos/);
    assert.match(ui.input.error, /11 dígitos/);
    ui.changeRuc('20330791412');
    await ui.click();
    assert.equal(calls, 1);
    assert.match(ui.status.textContent, /puedes continuar/);
    assert.equal(ui.query.disabled, false);
});

test('consulta pública envía POST con CSRF sin exponer los datos en la URL', async () => {
    let called;
    const ui = setup(async (url, options) => {
        called = { url, options };
        return { ok: false, status: 429 };
    });
    ui.panel.dataset.method = 'POST';
    await ui.click();
    assert.equal(called.url.search, '');
    assert.equal(called.options.method, 'POST');
    assert.equal(called.options.headers['X-CSRF-TOKEN'], 'csrf-test');
    assert.equal(JSON.parse(called.options.body).ruc, '20330791412');
    assert.match(ui.status.textContent, /manualmente/);
});
