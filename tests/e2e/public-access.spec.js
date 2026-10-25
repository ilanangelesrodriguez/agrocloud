import { test, expect } from '@playwright/test';

test('navegador permite ir del login a recuperar contraseña y regresar', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('link', { name: '¿Olvidaste tu contraseña?' }).click();
    await expect(page).toHaveURL(/\/recuperar-contrasena$/);
    await expect(page.getByRole('heading', { name: 'Recuperar contraseña', exact: true })).toBeVisible();
    await expect(page.getByLabel('Correo electrónico')).toHaveAttribute('type', 'email');
    await page.getByRole('link', { name: 'Volver al inicio de sesión' }).click();
    await expect(page).toHaveURL(/\/login$/);
});

test('navegador muestra validación del correo sin perder el formulario', async ({ page }) => {
    await page.goto('/recuperar-contrasena');
    await page.locator('form').evaluate((form) => { form.noValidate = true; });
    await page.getByLabel('Correo electrónico').fill('correo-invalido');
    await page.getByRole('button', { name: 'Enviar enlace', exact: true }).click();
    await expect(page.locator('#correo')).toHaveClass(/border-red-400/);
    await expect(page.locator('#correo')).toHaveValue('correo-invalido');
    await expect(page).toHaveURL(/\/recuperar-contrasena$/);
});

test('navegador rechaza contraseña débil antes de procesar el token', async ({ page }) => {
    await page.goto('/restablecer-contrasena/token-de-prueba?email=ana%40example.test');
    await page.locator('form').evaluate((form) => { form.noValidate = true; });
    await page.getByLabel('Nueva contraseña', { exact: true }).fill('Corta123');
    await page.getByLabel('Confirmar contraseña', { exact: true }).fill('Corta123');
    await page.getByRole('button', { name: 'Guardar contraseña', exact: true }).click();
    await expect(page.locator('#password')).toHaveClass(/border-red-400/);
    await expect(page.locator('#correo')).toHaveValue('ana@example.test');
    await expect(page.locator('#password')).toHaveValue('');
});
