const initialized = new WeakSet();
function initialize() {
    document.querySelectorAll('[data-quality-decision]').forEach(form => {
        if (initialized.has(form)) return;
        initialized.add(form);
        const update = () => {
            const decision = form.querySelector('input[name="decision"]:checked')?.value;
            const motive = form.querySelector('select[name="motivo"]');
            if (motive) { motive.disabled = !['OBSERVADO', 'RECHAZADO'].includes(decision); motive.parentElement.hidden = motive.disabled; if (motive.disabled) motive.value = ''; }
            const reinspection = form.elements.tipo_observacion?.value === 'REINSPECCION';
            if (form.elements.tipo_observacion) { form.elements.tipo_observacion.disabled = decision !== 'OBSERVADO'; form.elements.tipo_observacion.parentElement.hidden = decision !== 'OBSERVADO'; }
            form.dataset.confirmTone = decision === 'RECHAZADO' ? 'danger' : 'primary';
            const protocol = form.elements.protocolo_calidad.value.trim();
            const notes = form.elements.observaciones.value.trim();
            const independent = form.elements.revision_independiente.checked;
            form.querySelector('[data-quality-ready]').textContent = `Decisión: ${decision || 'pendiente'} · Protocolo: ${protocol ? 'registrado' : 'pendiente'} · Fundamento: ${notes ? 'registrado' : 'pendiente'} · Revisión independiente: ${independent ? 'confirmada' : 'pendiente'}.`;
            form.dataset.confirm = `Lote: ${form.dataset.qualityLot}. Decisión: ${decision || 'sin seleccionar'}. Supervisor: ${form.dataset.qualitySupervisor}. Protocolo: ${protocol || 'pendiente'}. ${decision === 'OBSERVADO' ? (reinspection ? 'Se abrirá una reinspección conservando este expediente.' : 'El lote quedará observado hasta revisar la documentación o corrección; no se abrirá una nueva inspección.') : decision === 'APROBADO' ? 'Se autorizará la liberación del lote.' : 'Se registrará el rechazo del lote.'} La decisión no se podrá duplicar ni editar desde este expediente.`;
        };
        form.addEventListener('change', update); form.addEventListener('input', update); update();
    });
}
document.addEventListener('livewire:navigated', initialize);
document.addEventListener('DOMContentLoaded', initialize);
if (document.readyState !== 'loading') initialize();
