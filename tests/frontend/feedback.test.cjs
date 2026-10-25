const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function harness() {
    const listeners = new Map();

    class Element {
        constructor(tag) {
            this.tag = tag;
            this.children = [];
            this.parentNode = null;
            this.attributes = {};
            this.classList = { add() {}, toggle() {} };
        }
        setAttribute(name, value) { this.attributes[name] = value; }
        addEventListener(name, callback) { (this.listeners ||= {})[name] = callback; }
        append(...nodes) {
            for (const node of nodes) {
                if (node.parentNode) node.parentNode.children = node.parentNode.children.filter((item) => item !== node);
                node.parentNode = this;
                this.children.push(node);
            }
        }
        remove() {
            if (this.parentNode) this.parentNode.children = this.parentNode.children.filter((item) => item !== this);
            this.parentNode = null;
        }
        get firstElementChild() { return this.children[0] || null; }
        querySelector(selector) {
            if (selector === '[data-modal-toast-container]') return this.children.find((child) => 'data-modal-toast-container' in child.attributes) || null;
            return null;
        }
        matches(selector) { return selector === 'dialog' && this.tag === 'dialog'; }
    }

    const globalHost = new Element('div');
    const drawer = new Element('dialog');
    let modalOpen = false;
    const document = {
        activeElement: { closest: (selector) => modalOpen && selector === 'dialog:modal' ? drawer : null },
        querySelector: (selector) => selector === '[data-toast-container]' ? globalHost : null,
        querySelectorAll: (selector) => selector === 'dialog:modal' ? (modalOpen ? [drawer] : []) : [],
        createElement: (tag) => new Element(tag),
        addEventListener: (name, callback) => {
            const callbacks = listeners.get(name) || [];
            callbacks.push(callback);
            listeners.set(name, callbacks);
        },
    };
    const context = vm.createContext({ document, window: { setTimeout: () => 1, clearTimeout() {} } });
    const file = fs.readFileSync(path.join(__dirname, '../../resources/js/feedback.js'), 'utf8');
    vm.runInContext(file.replace(/\bexport\s+/g, '') + '\nglobalThis.__showToast = showToast;', context);

    return {
        showToast: context.__showToast,
        globalHost, drawer,
        setModalOpen: (value) => { modalOpen = value; },
        close: () => (listeners.get('close') || []).forEach((fn) => fn({ target: drawer })),
    };
}

test('toast de la página se muestra en el host global si no hay modal', () => {
    const ui = harness();
    ui.showToast('Guardado correctamente');
    assert.equal(ui.globalHost.children.length, 1);
    assert.equal(ui.drawer.children.length, 0);
});

test('toast de validación permanece visible dentro del drawer modal', () => {
    const ui = harness();
    ui.setModalOpen(true);
    ui.showToast('El dato no es válido', 'error');
    assert.equal(ui.globalHost.children.length, 0);
    const host = ui.drawer.querySelector('[data-modal-toast-container]');
    assert.ok(host);
    assert.equal(host.children.length, 1);
    assert.equal(host.children[0].attributes.role, 'alert');
});

test('un toast que sigue activo se traslada al cerrar el drawer', () => {
    const ui = harness();
    ui.setModalOpen(true);
    ui.showToast('Error de validación', 'error');
    ui.setModalOpen(false);
    ui.close();
    assert.equal(ui.drawer.querySelector('[data-modal-toast-container]'), null);
    assert.equal(ui.globalHost.children.length, 1);
});
