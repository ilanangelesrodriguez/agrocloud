/**
 * AgroCloud AI - Preferencia de apariencia.
 * La aplicación inicial del tema ocurre en <head>, antes de cargar el CSS,
 * para impedir el destello de la interfaz clara al abrir una página.
 */
const STORAGE_KEY = `agrocloud.theme.${document.documentElement.dataset.themeUser || 'guest'}`;
const VALID_THEMES = new Set(['light', 'dark', 'system']);
const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');

function getPreference() {
    try {
        const saved = window.localStorage.getItem(STORAGE_KEY);
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

    document.querySelectorAll('[data-theme-option]').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.themeOption === preference));
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

function setPreference(preference) {
    if (!VALID_THEMES.has(preference)) return;
    try {
        window.localStorage.setItem(STORAGE_KEY, preference);
    } catch {
        // La preferencia se aplica durante esta página incluso si el navegador
        // no permite almacenamiento persistente.
    }
    applyTheme(preference);
}

// Se activa sin depender de la interacción del menú de usuario.
systemTheme.addEventListener?.('change', () => {
    if (getPreference() === 'system') applyTheme('system');
});

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();

    document.querySelectorAll('[data-theme-option]').forEach((button) => {
        button.addEventListener('click', () => setPreference(button.dataset.themeOption));
    });
});

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
