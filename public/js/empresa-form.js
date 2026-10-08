/* 
 * Catálogo de UBIGEO (CC0): https://github.com/RitchieRD/ubigeos-peru-data
 * Fijado al commit para mantener consistencia entre departamentos, provincias y distritos.
 * Los campos continúan editables manualmente si no hay conexión al catálogo.
 */
(() => {
    'use strict';

    const SOURCE = 'https://raw.githubusercontent.com/RitchieRD/ubigeos-peru-data/23cd3666b19f32bccdf36d90243c2a4462d6202c/json/';
    const SOURCES = [
        '1_ubigeo_departamentos.json',
        '2_ubigeo_provincias.json',
        '3_ubigeo_distritos.json',
    ];
    const CACHE_KEY = 'agrocloud.ubigeo.catalogo.23cd3666';
    const MAX_LOGO_BYTES = 4 * 1024 * 1024;
    const LOGO_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    const normalize = (value) => String(value ?? '')
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .trim().replace(/\s+/g, ' ').toLocaleUpperCase('es-PE');

    const findByName = (items, value, key) =>
        items.find((item) => normalize(item[key]) === normalize(value)) ?? null;

    const fillDatalist = (element, items, key) => {
        if (!element) return;
        const fragment = document.createDocumentFragment();
        for (const item of items) {
            const option = document.createElement('option');
            option.value = item[key];
            fragment.appendChild(option);
        }
        element.replaceChildren(fragment);
    };

    const readCache = () => {
        try {
            const cached = JSON.parse(sessionStorage.getItem(CACHE_KEY) ?? 'null');
            if (cached && Array.isArray(cached.departamentos) && Array.isArray(cached.provincias)
                && Array.isArray(cached.distritos)) return cached;
        } catch { /* La navegación privada puede bloquear almacenamiento. */ }
        return null;
    };

    const loadCatalog = async () => {
        const cached = readCache();
        if (cached) return cached;

        const requests = SOURCES.map(async (file) => {
            const response = await fetch(SOURCE + file, { cache: 'force-cache' });
            if (!response.ok) throw new Error('El catálogo geográfico no está disponible.');
            return response.json();
        });
        const [departments, provinces, districts] = await Promise.all(requests);
        const catalog = {
            departamentos: departments.ubigeo_departamentos,
            provincias: provinces.ubigeo_provincias,
            distritos: districts.ubigeo_distritos,
        };
        if (!Array.isArray(catalog.departamentos) || !Array.isArray(catalog.provincias)
            || !Array.isArray(catalog.distritos) || catalog.departamentos.length < 20
            || catalog.provincias.length < 150 || catalog.distritos.length < 1500) {
            throw new Error('El catálogo de ubicaciones tiene datos incompletos.');
        }
        try { sessionStorage.setItem(CACHE_KEY, JSON.stringify(catalog)); } catch { /* Opcional. */ }
        return catalog;
    };

    const initLocation = async (form) => {
        const section = form.querySelector('[data-company-location]');
        if (!section) return;
        const fields = {
            departamento: section.querySelector('[data-geo-field="departamento"]'),
            provincia: section.querySelector('[data-geo-field="provincia"]'),
            distrito: section.querySelector('[data-geo-field="distrito"]'),
            ubigeo: section.querySelector('[data-geo-field="ubigeo"]'),
        };
        const status = section.querySelector('[data-geo-status]');
        let changed = false;
        let catalog;
        try {
            catalog = await loadCatalog();
        } catch {
            // Nunca bloqueamos la edición de un tenant existente si GitHub no está disponible.
            status.textContent = 'No se pudo cargar el listado de ubicaciones. Puedes escribir departamento, provincia, distrito y UBIGEO manualmente. Comprueba los datos antes de guardar.';
            status.classList.remove('bg-brand-50', 'text-brand-800');
            status.classList.add('bg-amber-50', 'text-amber-800');
            return;
        }

        const { departamentos, provincias, distritos } = catalog;
        const options = {
            departamentos: section.querySelector('#company-departments'),
            provincias: section.querySelector('#company-provinces'),
            distritos: section.querySelector('#company-districts'),
        };
        let selectedDepartment = null;
        let selectedProvince = null;
        let selectedDistrict = null;

        const provincesOf = () => selectedDepartment
            ? provincias.filter((item) => Number(item.departamento_id) === Number(selectedDepartment.id)) : [];
        const districtsOf = () => selectedProvince
            ? distritos.filter((item) => Number(item.provincia_id) === Number(selectedProvince.id)) : [];

        const sync = (initial = false) => {
            const nextDepartment = findByName(departamentos, fields.departamento.value, 'departamento');
            const departmentChanged = selectedDepartment?.id !== nextDepartment?.id;
            selectedDepartment = nextDepartment;
            if (departmentChanged && !initial) {
                fields.provincia.value = '';
                fields.distrito.value = '';
            }
            fillDatalist(options.provincias, provincesOf(), 'provincia');
            fields.provincia.disabled = !selectedDepartment && !fields.provincia.value;
            fields.provincia.placeholder = selectedDepartment ? 'Escribe o elige una provincia' : 'Primero elige un departamento';

            const nextProvince = findByName(provincesOf(), fields.provincia.value, 'provincia');
            const provinceChanged = selectedProvince?.id !== nextProvince?.id;
            selectedProvince = nextProvince;
            if (provinceChanged && !initial) fields.distrito.value = '';
            fillDatalist(options.distritos, districtsOf(), 'distrito');
            fields.distrito.disabled = !selectedProvince && !fields.distrito.value;
            fields.distrito.placeholder = selectedProvince ? 'Escribe o elige un distrito' : 'Primero elige una provincia';

            selectedDistrict = findByName(districtsOf(), fields.distrito.value, 'distrito');
            if (selectedDistrict) {
                fields.ubigeo.value = String(selectedDistrict.ubigeo).padStart(6, '0');
                fields.ubigeo.readOnly = true;
            } else {
                if (!initial && changed) fields.ubigeo.value = '';
                fields.ubigeo.readOnly = false;
            }
        };

        fillDatalist(options.departamentos, departamentos, 'departamento');
        sync(true);
        status.textContent = 'Ubicación: busca un departamento, luego una provincia y finalmente un distrito. El UBIGEO se completa automáticamente. Los tres campos son opcionales.';

        for (const key of ['departamento', 'provincia', 'distrito']) {
            const input = fields[key];
            input.addEventListener('input', () => {
                changed = true;
                for (const item of ['departamento', 'provincia', 'distrito']) {
                    fields[item].setCustomValidity('');
                }
                sync();
            });
            input.addEventListener('change', () => {
                // Respeta mayúsculas/minúsculas en las consultas, guarda el nombre del catálogo.
                const item = key === 'departamento' ? selectedDepartment :
                    key === 'provincia' ? selectedProvince : selectedDistrict;
                if (item) input.value = item[key];
            });
        }

        const parentForm = form.closest('form');
        parentForm?.addEventListener('submit', (event) => {
            if (!changed) return; // No invalidar registros heredados que el usuario no haya tocado.
            const checks = [
                ['departamento', fields.departamento.value && !selectedDepartment, 'Selecciona un departamento de la lista.'],
                ['provincia', fields.provincia.value && !selectedProvince, 'Selecciona una provincia correspondiente al departamento.'],
                ['distrito', fields.distrito.value && !selectedDistrict, 'Selecciona un distrito correspondiente a la provincia.'],
            ];
            const invalid = checks.find(([key, mismatch, message]) => {
                fields[key].setCustomValidity(mismatch ? message : '');
                return Boolean(mismatch);
            });
            if (invalid) {
                event.preventDefault();
                fields[invalid[0]].reportValidity();
                fields[invalid[0]].focus();
            }
        });
    };

    const initLogo = (form) => {
        const input = form.querySelector('[data-logo-input]');
        const image = form.querySelector('[data-logo-image]');
        const empty = form.querySelector('[data-logo-empty]');
        const feedback = form.querySelector('[data-logo-feedback]');
        const dropzone = form.querySelector('[data-logo-dropzone]');
        const trigger = form.querySelector('[data-logo-trigger]');
        if (!input || !image || !empty || !feedback || !dropzone) return;
        const previousSrc = image.getAttribute('src');
        const defaultMessage = feedback.textContent;
        let objectUrl = null;
        const releaseUrl = () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        };
        const show = (src) => {
            if (src) {
                image.src = src;
                image.classList.remove('hidden');
                empty.classList.add('hidden');
            } else {
                image.removeAttribute('src');
                image.classList.add('hidden');
                empty.classList.remove('hidden');
            }
        };
        const previewFile = (file) => {
            releaseUrl();
            feedback.classList.remove('text-red-600');
            if (!file) {
                show(previousSrc);
                feedback.textContent = defaultMessage;
                return;
            }
            if (!LOGO_TYPES.includes(file.type) || file.size > MAX_LOGO_BYTES) {
                input.value = '';
                show(previousSrc);
                feedback.classList.add('text-red-600');
                feedback.textContent = file.size > MAX_LOGO_BYTES
                    ? 'La imagen supera los 4 MB. Selecciona una imagen más ligera.'
                    : 'Selecciona un archivo PNG, JPG o WEBP.';
                return;
            }
            objectUrl = URL.createObjectURL(file);
            show(objectUrl);
            feedback.textContent = `${file.name} · ${(file.size / (1024 * 1024)).toFixed(2)} MB · vista previa (aún no guardado)`;
        };
        input.addEventListener('change', () => previewFile(input.files?.[0]));
        trigger?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                input.click();
            }
        });
        for (const type of ['dragenter', 'dragover']) {
            dropzone.addEventListener(type, (event) => {
                event.preventDefault();
                dropzone.classList.add('border-brand-500', 'bg-brand-50');
            });
        }
        for (const type of ['dragleave', 'drop']) {
            dropzone.addEventListener(type, (event) => {
                event.preventDefault();
                dropzone.classList.remove('border-brand-500', 'bg-brand-50');
            });
        }
        dropzone.addEventListener('drop', (event) => {
            const files = event.dataTransfer?.files;
            if (!files?.length) return;
            try {
                const transfer = new DataTransfer();
                transfer.items.add(files[0]);
                input.files = transfer.files;
                previewFile(input.files[0]);
            } catch {
                feedback.textContent = 'Tu navegador no permite arrastrar archivos aquí. Usa «Seleccionar imagen».';
            }
        });
        window.addEventListener('pagehide', releaseUrl, { once: true });
    };

    const initDescription = (form) => {
        const input = form.querySelector('[data-company-description]');
        const counter = form.querySelector('[data-description-counter]');
        if (!input || !counter) return;
        const update = () => { counter.textContent = `${input.value.length} / 1000`; };
        input.addEventListener('input', update);
        update();
    };

    document.addEventListener('DOMContentLoaded', () => {
        for (const form of document.querySelectorAll('[data-company-form]')) {
            initDescription(form);
            initLogo(form);
            void initLocation(form);
        }
    });
})();
