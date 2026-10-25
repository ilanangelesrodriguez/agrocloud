let opener;
let groups = [];
let index = 0;
let zoom = 1;
const dialog = () => document.querySelector('[data-image-dialog]');
function scale() {
    dialog()?.querySelectorAll('[data-viewer-image]').forEach(img => { img.style.width = `${zoom * 100}%`; });
    dialog().querySelector('[data-viewer-reset]').textContent = `${Math.round(zoom * 100)} %`;
    dialog().querySelector('[data-viewer-out]').disabled = zoom <= 1;
    dialog().querySelector('[data-viewer-in]').disabled = zoom >= 4;
}
function render() {
    const viewer = dialog(); const group = groups[index]; zoom = 1;
    viewer.querySelector('#image-dialog-title').textContent = `Evidencia ${group.id}`;
    viewer.querySelector('[data-viewer-position]').textContent = `${index + 1} / ${groups.length}`;
    viewer.querySelector('[data-viewer-prev]').disabled = index === 0;
    viewer.querySelector('[data-viewer-next]').disabled = index === groups.length - 1;
    for (const kind of ['original', 'resultado']) {
        const image = viewer.querySelector(`[data-viewer-image="${kind}"]`); const source = group[kind];
        image.hidden = !source; if (source) image.src = source; else image.removeAttribute('src');
        viewer.querySelector(`[data-viewer-empty="${kind}"]`).hidden = Boolean(source);
        const pane = viewer.querySelector(`[data-viewer-pane="${kind}"]`); pane.scrollTop = 0; pane.scrollLeft = 0;
    }
    scale();
}
document.addEventListener('click', event => {
    const link = event.target.closest('[data-image-zoom]');
    if (link && dialog() && event.button === 0 && !event.ctrlKey && !event.metaKey && !event.shiftKey) {
        event.preventDefault(); opener = link;
        const map = new Map();
        document.querySelectorAll('[data-image-zoom]').forEach(item => {
            const id = item.dataset.imageGroup; if (!id) return;
            const group = map.get(id) || { id }; group[item.dataset.imageKind || 'original'] = item.href || item.dataset.imageSrc; map.set(id, group);
        });
        groups = [...map.values()]; index = groups.findIndex(group => group.id === link.dataset.imageGroup);
        if (index < 0) return; render(); dialog().showModal(); return;
    }
    const viewer = dialog(); if (!viewer?.open) return;
    if (event.target.closest('[data-viewer-close]')) viewer.close();
    else if (event.target.closest('[data-viewer-next]') && index < groups.length - 1) { index++; render(); }
    else if (event.target.closest('[data-viewer-prev]') && index > 0) { index--; render(); }
    else if (event.target.closest('[data-viewer-in]')) { zoom = Math.min(4, zoom + 0.25); scale(); }
    else if (event.target.closest('[data-viewer-out]')) { zoom = Math.max(1, zoom - 0.25); scale(); }
    else if (event.target.closest('[data-viewer-reset]')) { zoom = 1; scale(); }
});
document.addEventListener('close', event => { if (event.target.matches('[data-image-dialog]')) { if (opener?.isConnected) opener.focus(); event.target.querySelectorAll('[data-viewer-image]').forEach(img => img.removeAttribute('src')); } }, true);
document.addEventListener('livewire:navigating', () => dialog()?.close());

document.addEventListener('keydown', event => { if (['Enter', ' '].includes(event.key) && event.target.matches('img[data-image-zoom]')) { event.preventDefault(); event.target.click(); } });
