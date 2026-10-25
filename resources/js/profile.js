let photoUrl;
const profileForms = new WeakSet();
function cleanupProfile() { if (photoUrl) URL.revokeObjectURL(photoUrl); photoUrl = null; }
function initializeProfile() {
    const form = document.querySelector('[data-profile-form]');
    if (!form || profileForms.has(form)) return;
    profileForms.add(form);
    const input = form.querySelector('[data-profile-input]');
    input.addEventListener('change', () => {
        cleanupProfile();
        const image = form.querySelector('[data-profile-image]');
        const empty = form.querySelector('[data-profile-empty]');
        const file = input.files?.[0];
        const valid = !file || (['image/jpeg', 'image/png', 'image/webp'].includes(file.type) && file.size <= 2 * 1024 * 1024);
        input.setCustomValidity(valid ? '' : 'Selecciona una imagen JPG, PNG o WebP de hasta 2 MB.');
        const status = form.querySelector('[data-profile-file-meta]');
        status.textContent = valid ? (file ? file.name : 'JPG, PNG o WebP · máximo 2 MB.') : input.validationMessage;
        if (file && valid) { photoUrl = URL.createObjectURL(file); image.src = photoUrl; }
        else if (image.dataset.originalPhoto) image.src = image.dataset.originalPhoto;
        else image.removeAttribute('src');
        image.classList.toggle('hidden', !photoUrl && !image.dataset.originalPhoto);
        empty.classList.toggle('hidden', Boolean(photoUrl || image.dataset.originalPhoto));
    });
}
document.addEventListener('DOMContentLoaded', initializeProfile);
document.addEventListener('livewire:navigated', initializeProfile);
document.addEventListener('livewire:navigating', cleanupProfile);
window.addEventListener('pagehide', cleanupProfile);
if (document.readyState !== 'loading') initializeProfile();
