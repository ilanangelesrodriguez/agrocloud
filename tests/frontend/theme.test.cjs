const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');

test('los botones de tema sincronizan estado, etiqueta y preferencia guardada', async () => {
    const handlers = {};
    const classes = new Set();
    const buttons = [0, 1].map(() => ({ attributes: {}, setAttribute(key, value) { this.attributes[key] = value; } }));
    const label = {};
    const dataset = { themeUser: '1', themePreference: 'light' };
    const requests = [];
    const document = {
        readyState: 'loading', documentElement: { dataset, style: {}, classList: {
            toggle(key, enabled) { enabled ? classes.add(key) : classes.delete(key); },
            contains: key => classes.has(key), remove: key => classes.delete(key),
        } },
        querySelectorAll: selector => selector === '[data-theme-switch]' ? buttons : [label],
        querySelector: selector => selector.includes('theme-preference-url') ? { content: '/perfil/tema' } : selector.includes('csrf-token') ? { content: 'token' } : null,
        addEventListener: (key, fn) => { handlers[key] = fn; },
    };
    const context = vm.createContext({ document, showToast() {},
        window: { localStorage: { getItem: () => null, setItem() {} }, matchMedia: () => ({ matches: false, addEventListener() {} }), addEventListener() {} },
        fetch: async (url, options) => { requests.push(JSON.parse(options.body)); return { ok: true, json: async () => ({ theme: 'dark' }) }; },
    });
    vm.runInContext(fs.readFileSync(path.join(__dirname, '../../resources/js/theme.js'), 'utf8').replace(/^import .*;\r?\n/gm, ''), context);
    handlers.DOMContentLoaded();
    assert.equal(label.textContent, 'Activar modo oscuro');
    handlers.click({ target: { closest: () => buttons[1] } });
    await new Promise(resolve => setTimeout(resolve, 5));
    for (const button of buttons) {
        assert.equal(button.attributes['aria-pressed'], 'true');
        assert.equal(button.attributes['aria-label'], 'Activar modo claro');
        assert.equal(button.disabled, false);
    }
    assert.equal(label.textContent, 'Activar modo claro');
    assert.equal(dataset.themePreference, 'dark');
    assert.equal(requests[0].theme, 'dark');
});
