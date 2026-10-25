import { chromium } from '@playwright/test';
import fs from 'node:fs/promises';

export default async function setup(config) {
    if (process.env.CI !== 'true' || process.env.APP_ENV !== 'testing' || process.env.AGROCLOUD_ALLOW_PG_TESTS !== '1' || !process.env.QA_E2E_PASSWORD) {
        throw new Error('E2E autenticado requiere CI y Neon testing verificado.');
    }
    await fs.mkdir('playwright/.auth', { recursive: true });
    const browser = await chromium.launch();
    try {
        for (const [role, email] of [['admin', 'qa-admin@example.test'], ['root', 'qa-root@example.test']]) {
            // Contexto separado: nunca grabar credenciales en trazas o capturas.
            const context = await browser.newContext({ baseURL: config.projects[0].use.baseURL });
            const page = await context.newPage();
            await page.goto('/login');
            await page.locator('[name="correo"]').fill(email);
            await page.locator('[name="password"]').fill(process.env.QA_E2E_PASSWORD);
            await page.getByRole('button', { name: 'Iniciar sesión', exact: true }).click();
            await page.waitForURL('**/dashboard');
            await context.storageState({ path: `playwright/.auth/${role}.json` });
            await context.close();
        }
    } finally { await browser.close(); }
}
