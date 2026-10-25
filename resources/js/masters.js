const initialized = new WeakSet();
const normalize = (value) => (value || '').trim().toLocaleLowerCase('es-PE');

function initializeMasters() {
    document.querySelectorAll('[data-master-kind="variedad"]').forEach((variety) => {
        if (initialized.has(variety)) return;
        const product = variety.closest('form')?.querySelector('[data-master-kind="producto"]');
        if (!product) return;
        initialized.add(variety);
        const options = [...variety.options].filter((option) => option.value).map((option) => option.cloneNode(true));
        const initialProduct = product.value;
        const update = (initial = false) => {
            const selected = initial ? variety.value : '';
            const candidates = options.filter((option) => normalize(option.dataset.parent) === normalize(product.value)
                || (initial && initialProduct === product.value && option.hasAttribute('data-master-legacy')));
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = !product.value ? 'Primero selecciona un producto' : candidates.length ? 'Sin especificar' : 'Sin variedades disponibles';
            variety.replaceChildren(placeholder, ...candidates.map((option) => option.cloneNode(true)));
            variety.value = candidates.some((option) => option.value === selected) ? selected : '';
            variety.disabled = !product.value || !candidates.length;
        };
        product.addEventListener('change', () => update());
        variety.addEventListener('master:added', (event) => {
            options.push(event.detail.cloneNode(true));
            update(true);
        });
        update(true);
    });
    document.querySelectorAll('form').forEach((form) => {
        const decision = form.querySelector('select[name="decision"]');
        const motive = form.querySelector('select[name="motivo"]');
        if (!decision || !motive || initialized.has(motive)) return;
        initialized.add(motive);
        const update = () => {
            const visible = ['OBSERVADO', 'RECHAZADO'].includes(decision.value);
            motive.parentElement.hidden = !visible;
            motive.disabled = !visible;
            if (!visible) motive.value = '';
        };
        decision.addEventListener('change', update);
        update();
    });
}

document.addEventListener('DOMContentLoaded', initializeMasters);
document.addEventListener('livewire:navigated', initializeMasters);
if (document.readyState !== 'loading') initializeMasters();
