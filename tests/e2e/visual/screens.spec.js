import { test, expect } from '@playwright/test';

async function capture(page, info, url, mobile = false) {
    if (mobile) await page.setViewportSize({ width: 390, height: 844 });
    const response = await page.goto(url);
    expect(response.status()).toBe(200);
    await expect(page).not.toHaveURL(/\/login$/);
    await expect(page.locator('main').first()).toBeVisible();
    await page.evaluate(() => document.fonts.ready);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    expect(overflow, 'El documento no debe desbordar horizontalmente').toBe(false);
    const language = await page.locator('html').getAttribute('lang');
    expect(language).toBe('es');
    await expect(page.locator('h1, h2').first()).toBeVisible();
    const unnamed = await page.locator('main input:not([type="hidden"]):not([type="submit"]), main select, main textarea').evaluateAll((elements) => elements.filter((element) => element.getClientRects().length && !element.labels?.length && !element.getAttribute('aria-label') && !element.getAttribute('aria-labelledby') && element.type !== 'checkbox').map((element) => element.name || element.id));
    expect(unnamed, 'Los controles visibles deben tener una etiqueta accesible').toEqual([]);
    await info.attach('Página completa', { body: await page.screenshot({ fullPage: true, animations: 'disabled', mask: [page.locator('input[type="password"]')] }), contentType: 'image/png' });
}

test('Visual: dashboard de empresa en escritorio', async ({ page }, info) => { await capture(page, info, '/dashboard'); });
test('Visual: productores en escritorio', async ({ page }, info) => { await capture(page, info, '/productores'); });
test('Visual: lotes en escritorio', async ({ page }, info) => { await capture(page, info, '/lotes'); });
test('Visual: formulario de productor en móvil', async ({ page }, info) => { await capture(page, info, '/productores/crear', true); });
test('Visual: dashboard de empresa en móvil', async ({ page }, info) => { await capture(page, info, '/dashboard', true); });

test.describe('Administración global', () => {
    test.use({ storageState: 'playwright/.auth/root.json' });
    test('Visual: empresas en escritorio', async ({ page }, info) => { await capture(page, info, '/admin/empresas'); });
    test('Visual: roles y permisos en escritorio', async ({ page }, info) => {
        await capture(page, info, '/roles');
        await capture(page, info, '/permisos');
    });
});

test.describe('Solicitud pública con catálogos de testing', () => {
    test.use({ storageState: { cookies: [], origins: [] } });
    test('Visual: solicitud de acceso en móvil', async ({ page }, info) => { await capture(page, info, '/solicitar-acceso', true); });
});
