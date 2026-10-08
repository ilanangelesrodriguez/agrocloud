// Test de interacciones DOM sintéticas sin dependencias adicionales.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');

const SRC = fs.readFileSync(path.join(__dirname, '../../public/js/empresa-form.js'), 'utf8');

function element(value = '') {
  const handlers = {};
  const classes = new Set();
  return {
    value, placeholder: '', disabled: false, readOnly: false, textContent: '',
    handlers, children: [], attributes: {},
    classList: { add: (...x) => x.forEach(v => classes.add(v)), remove: (...x) => x.forEach(v => classes.delete(v)), contains: v => classes.has(v) },
    addEventListener: (type, fn) => { (handlers[type] ??= []).push(fn); },
    dispatch(type, obj = {}) { for (const fn of handlers[type] ?? []) fn(obj); },
    replaceChildren(fragment) { this.children = fragment.children; },
    appendChild(child) { this.children.push(child); },
    setCustomValidity(msg) { this.error = msg; },
    reportValidity() { this.reportCalled = true; },
    focus() { this.focused = true; },
    getAttribute(name) { return this.attributes[name] || ''; },
    removeAttribute(name) { delete this.attributes[name]; },
    click() { this.clicked = true; },
  };
}

function context({ existing = {}, failFetch = false } = {}) {
  const geo = {
    departamento: element(existing.departamento),
    provincia: element(existing.provincia),
    distrito: element(existing.distrito),
    ubigeo: element(existing.ubigeo),
  };
  const status = element();
  const lists = ['company-departments', 'company-provinces', 'company-districts'].map(() => element());
  const logoInput = element();
  const logoImage = element();
  logoImage.attributes.src = existing.logo || '';
  const logoEmpty = element();
  const logoFeedback = element();
  logoFeedback.textContent = 'Sin logo';
  const logoDrop = element();
  const logoTrigger = element();
  const description = element('Cultivo de arándanos');
  const counter = element();
  const parent = element();
  const selectors = {
    '[data-company-location]': null,
    '[data-logo-input]': logoInput,
    '[data-logo-image]': logoImage,
    '[data-logo-empty]': logoEmpty,
    '[data-logo-feedback]': logoFeedback,
    '[data-logo-dropzone]': logoDrop,
    '[data-logo-trigger]': logoTrigger,
    '[data-company-description]': description,
    '[data-description-counter]': counter,
  };
  const section = {
    querySelector(sel) {
      if (sel.startsWith('[data-geo-field')) return geo[sel.match(/"([^\"]+)"/)[1]];
      if (sel === '[data-geo-status]') return status;
      if (sel.startsWith('#company-')) return lists[['#company-departments', '#company-provinces', '#company-districts'].indexOf(sel)];
      return null;
    },
  };
  selectors['[data-company-location]'] = section;
  const root = {querySelector: selector => selectors[selector], closest: () => parent};
  const doc = {handlers: {}, addEventListener(event, fn) { this.handlers[event] = fn; }, querySelectorAll: () => [root], createElement: () => element(), createDocumentFragment: () => element()};
  const departments = Array.from({length: 25}, (_,i)=>({id:i+1, departamento:i===14?'LIMA':`REGION ${i+1}`,ubigeo:String(i+1).padStart(2,'0')}));
  const provinces = Array.from({length: 195},(_,i)=>({id:i+1, provincia:i===0?'LIMA':`PROVINCIA ${i+1}`, departamento_id:i===0?15:Math.floor(i/8)+1}));
  const districts = Array.from({length: 1890},(_,i)=>({id:i+1, distrito:i===0?'MIRAFLORES':`DISTRITO ${i+1}`, provincia_id:i===0?1:Math.floor(i/10)+1, ubigeo:i===0?'150122':String(i+1).padStart(6,'0')}));
  const jsons = [ {ubigeo_departamentos:departments}, {ubigeo_provincias:provinces}, {ubigeo_distritos:districts} ];
  const env = {
    document: doc, URL: { createObjectURL:()=> 'blob:preview', revokeObjectURL:()=>{} },
    window: { addEventListener: () => {} },
    sessionStorage: { getItem:()=>null, setItem:()=>{} },
    fetch: async url => { if (failFetch) throw Error('offline'); return { ok:true, json:async()=> jsons[['1_ubigeo_departamentos.json','2_ubigeo_provincias.json','3_ubigeo_distritos.json'].findIndex(x=>url.endsWith(x))] }; },
    console, File: class {},
  };
  vm.runInNewContext(SRC,env);
  doc.handlers.DOMContentLoaded();
  return { geo, status, lists, root, parent, logoInput, logoImage, logoEmpty, logoFeedback, counter };
}
const flush = () => new Promise(resolve => setTimeout(resolve, 10));

test('al editar una empresa resuelve UBIGEO y opciones relacionadas', async () => {
  const ctx = context({existing:{departamento:'Lima',provincia:'Lima',distrito:'Miraflores',ubigeo:''}});
  await flush();
  assert.equal(ctx.geo.ubigeo.value,'150122');
  assert.equal(ctx.geo.ubigeo.readOnly,true);
  assert.ok(ctx.lists[0].children.length>=25);
  assert.ok(ctx.lists[1].children.length>=1);
  assert.ok(ctx.lists[2].children.length>=1);
  assert.match(ctx.status.textContent,/automáticamente/);
});

test('si cambias departamento limpia valores dependientes', async () => {
  const ctx=context({existing:{departamento:'Lima',provincia:'Lima',distrito:'Miraflores',ubigeo:'150122'}});
  await flush();
  ctx.geo.departamento.value='REGION 2';ctx.geo.departamento.dispatch('input');
  assert.equal(ctx.geo.provincia.value,'');
  assert.equal(ctx.geo.distrito.value,'');
  assert.equal(ctx.geo.ubigeo.value,'');
  assert.equal(ctx.geo.distrito.disabled,true);
});

test('bloquea provincia inválida al guardar después de modificar ubicación', async () => {
  const ctx=context(); await flush();
  ctx.geo.departamento.value='LIMA';ctx.geo.departamento.dispatch('input');
  ctx.geo.provincia.value='NO EXISTE';ctx.geo.provincia.dispatch('input');
  let prevented=false;
  ctx.parent.dispatch('submit',{preventDefault(){prevented=true;}});
  assert.equal(prevented,true);
  assert.match(ctx.geo.provincia.error,/Selecciona una provincia/);
});

test('si no carga el catálogo los campos permanecen editables', async () => {
  const ctx=context({failFetch:true,existing:{provincia:'Ancón',distrito:'Lima'}}); await flush();
  assert.equal(ctx.geo.provincia.disabled,false);
  assert.equal(ctx.geo.distrito.disabled,false);
  assert.match(ctx.status.textContent,/manualmente/);
});

test('contador de descripción y error para logo demasiado grande',async()=>{
  const ctx=context();await flush();
  assert.equal(ctx.counter.textContent,'20 / 1000');
  ctx.logoInput.files=[{name:'foto.png',type:'image/png',size:5*1024*1024}];
  ctx.logoInput.dispatch('change');
  assert.match(ctx.logoFeedback.textContent,/supera los 4 MB/);
});
