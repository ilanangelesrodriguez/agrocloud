import { showToast } from './feedback';

/**
 * AgroCloud AI - Preferencia de apariencia.
 * La aplicación inicial del tema ocurre en <head>, antes de cargar el CSS,
 * para impedir el destello de la interfaz clara al abrir una página.
 */
const storageKey = () => `agrocloud.theme.${document.documentElement.dataset.themeUser || 'guest'}`;
const VALID_THEMES = new Set(['light', 'dark', 'system']);
const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
let pendingTheme = null;
let saving = false;

function getPreference() {
    if (pendingTheme?.account === storageKey()) return pendingTheme.value;
    const serverPreference = document.documentElement.dataset.themePreference;
    if (VALID_THEMES.has(serverPreference)) return serverPreference;
    try {
        const saved = window.localStorage.getItem(storageKey());
        return VALID_THEMES.has(saved) ? saved : 'system';
    } catch {
        return 'system';
    }
}

function applyTheme(preference = getPreference()) {
    const resolved = preference === 'system'
        ? (systemTheme.matches ? 'dark' : 'light')
        : preference;

    document.documentElement.classList.toggle('dark', resolved === 'dark');
    document.documentElement.style.colorScheme = resolved;
    document.documentElement.dataset.theme = resolved;

    document.querySelectorAll('[data-theme-switch]').forEach((button) => {
        button.setAttribute('aria-pressed', String(resolved === 'dark'));
        button.setAttribute('aria-label', resolved === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro');
        button.title = resolved === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro';
        button.disabled = saving;
        button.setAttribute('aria-busy', String(saving));
    });
    document.querySelectorAll('[data-theme-action-label]').forEach((label) => {
        label.textContent = resolved === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro';
    });

    const current = document.querySelector('[data-theme-current]');
    if (current) {
        current.textContent = {
            light: 'Claro',
            dark: 'Oscuro',
            system: 'Sistema',
        }[preference];
    }
}

function cachePreference(preference) {
    try {
        window.localStorage.setItem(storageKey(), preference);
    } catch { /* El servidor conserva la preferencia aunque el almacenamiento local esté bloqueado. */ }
}

async function setPreference(preference) {
    if (!VALID_THEMES.has(preference) || saving) return;
    const previous = getPreference();
    const account = storageKey();
    const url = document.querySelector('meta[name="theme-preference-url"]')?.content;
    pendingTheme = { account, value: preference };
    saving = Boolean(url);
    cachePreference(preference);
    applyTheme(preference);
    if (!url) {
        document.documentElement.dataset.themePreference = preference;
        pendingTheme = null;
        return;
    }
    try {
        const response = await fetch(url, {
            method: 'PUT', credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify({ theme: preference }),
        });
        if (!response.ok) throw new Error('No se pudo guardar el tema.');
        const result = await response.json();
        if (!VALID_THEMES.has(result.theme)) throw new Error('Preferencia inválida.');
        if (storageKey() === account) document.documentElement.dataset.themePreference = result.theme;
    } catch {
        if (storageKey() === account) {
            document.documentElement.dataset.themePreference = previous;
            cachePreference(previous);
            showToast('No se pudo guardar el tema. Inténtalo nuevamente.', 'error');
        }
    } finally {
        pendingTheme = null;
        saving = false;
        applyTheme();
    }
}

// Se activa sin depender de la interacción del menú de usuario.
systemTheme.addEventListener?.('change', () => {
    if (getPreference() === 'system') applyTheme('system');
});

const initializeThemeControls = () => {
    applyTheme();
};

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-switch]')) return;
    void setPreference(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
});

document.addEventListener('DOMContentLoaded', initializeThemeControls);
document.addEventListener('livewire:navigated', initializeThemeControls);
if (document.readyState !== 'loading') initializeThemeControls();

// Al imprimir, usar el tema claro para evitar gastar tinta y preservar legibilidad.
let beforePrintDark = false;
window.addEventListener('beforeprint', () => {
    beforePrintDark = document.documentElement.classList.contains('dark');
    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = 'light';
});
window.addEventListener('afterprint', () => {
    if (beforePrintDark) applyTheme();
    beforePrintDark = false;
});
