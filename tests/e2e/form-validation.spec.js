import { test, expect } from '@playwright/test';
import fs from 'node:fs';

const validation = fs.readFileSync(new URL('../../resources/js/form-validation.js', import.meta.url), 'utf8').replaceAll('export function', 'function');

async function formPage(page) {
    await page.setContent(`<form data-ajax-form>
        <label for="ruc">RUC *</label><input id="ruc" name="ruc" required pattern="[0-9]{11}" aria-describedby="ruc-hint"><p id="ruc-hint">Sin guiones</p>
        <label for="correo">Correo *</label><input id="correo" name="correo" type="email" required>
        <label for="tipo">Tipo *</label><select id="tipo" name="tipo_empresa" required><option value="">Selecciona</option><option value="Agrícola">Agrícola</option></select>
        <label><input type="checkbox" name="consentimiento" required>Autorizo el uso de datos</label>
        <button type="submit">Enviar</button>
        </form><div data-toast-container></div>`);
    await page.addScriptTag({ content: validation });
}

test('errores nativos quedan junto a cada campo y se limpian al corregir, sin toast', async ({ page }) => {
    await formPage(page);
    await page.getByRole('button', { name: 'Enviar' }).click();
    await expect(page.locator('[aria-invalid="true"]')).toHaveCount(4);
    await expect(page.locator('[data-form-field-error]')).toHaveCount(4);
    await expect(page.locator('#ruc')).toBeFocused();
    await expect(page.locator('[data-toast-container]')).toBeEmpty();
    await page.locator('#ruc').fill('123');
    await expect(page.locator('[data-form-field-error="ruc"]')).toContainText('11 dígitos');
    await page.locator('#ruc').fill('20330791412');
    await expect(page.locator('#ruc')).not.toHaveAttribute('aria-invalid');
    await expect(page.locator('#ruc')).toHaveAttribute('aria-describedby', 'ruc-hint');
    await expect(page.locator('[data-form-field-error="ruc"]')).toHaveCount(0);
    await page.locator('#tipo').selectOption('Agrícola');
    await expect(page.locator('[data-form-field-error="tipo_empresa"]')).toHaveCount(0);
    await page.locator('[name="consentimiento"]').check();
    await expect(page.locator('[data-form-field-error="consentimiento"]')).toHaveCount(0);
});

test('errores del servidor no se duplican y mantienen el texto seguro', async ({ page }) => {
    await formPage(page);
    await page.evaluate(() => {
        showFormErrors(document.querySelector('form'), { correo: ['Este correo requiere revisión.'], tipo_empresa: ['Selecciona un tipo.'] });
        showFormErrors(document.querySelector('form'), { correo: ['Correo <script>inesperado</script>'] });
    });
    await expect(page.locator('[data-form-field-error]')).toHaveCount(1);
    await expect(page.locator('[data-form-field-error="correo"]')).toHaveText('Correo <script>inesperado</script>');
    await expect(page.locator('[data-form-field-error] script')).toHaveCount(0);
    await expect(page.locator('#correo')).toBeFocused();
    await expect(page.locator('[data-toast-container]')).toBeEmpty();
});
