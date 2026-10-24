import { test, expect } from '@playwright/test';
import fs from 'node:fs';

test('E2E autenticado: navegar por dashboard, productores y lotes', async ({ page }) => {
    for (const [url, heading] of [['/dashboard', null], ['/productores', 'Productores'], ['/lotes', 'Lotes']]) {
        const response = await page.goto(url);
        expect(response.status()).toBe(200);
        await expect(page).not.toHaveURL(/\/login$/);
        if (heading) await expect(page.getByRole('heading', { name: heading, exact: true }).first()).toBeVisible();
        else await expect(page.locator('main')).toBeVisible();
    }
});

test('E2E autenticado: registrar, buscar y editar un productor ficticio', async ({ page }) => {
    const suffix = String(Date.now()).slice(-9);
    const name = `Productor ficticio E2E ${suffix}`;
    const response = await page.goto('/productores/crear/nuevo');

    expect(response.status()).toBe(200);

    await expect(
        page.locator('[name="ruc"]')
    ).toBeVisible();
    await page.locator('[name="ruc"]').fill(`10${suffix}`);
    await page.locator('[name="nombre_razon_social"]').fill(name);
    await page.locator('[name="fundo"]').fill('Fundo inicial QA');
    await page.getByRole('button', { name: 'Registrar productor', exact: true }).click();
    await expect(page).toHaveURL(/\/productores$/);
    await page.getByPlaceholder('Nombre, RUC, fundo o ubicación').fill(name);
    const row = page.locator('tbody tr').filter({ hasText: name });
    await expect(row).toHaveCount(1);
    const edit = await row.locator('a[href$="/editar"]').getAttribute('href');
    await page.goto(edit);
    await page.locator('[name="fundo"]').fill('Fundo actualizado QA');
    await page.getByRole('button', { name: 'Guardar cambios', exact: true }).click();
    await expect(page).toHaveURL(/\/productores\/\d+$/);
    await expect(page.getByText('Fundo actualizado QA', { exact: true })).toBeVisible();
});

test('E2E seguridad: impedir acceso a productor ajeno y administración global', async ({ page }) => {
    const fixtures = JSON.parse(fs.readFileSync('playwright/.auth/fixtures.json', 'utf8'));
    const foreign = await page.goto(`/productores/${fixtures.other_producer}`);
    expect(foreign.status()).toBe(404);
    await expect(page.getByText('Productor reservado QA Dos', { exact: true })).toHaveCount(0);
    const admin = await page.goto('/admin/empresas');
    expect(admin.status()).toBe(403);
});
