import { defineConfig } from '@playwright/test';
const suite = process.env.QA_BROWSER_SUITE || 'autenticado';
if (!['autenticado', 'visual'].includes(suite)) throw new Error('Suite de navegador desconocida.');

export default defineConfig({
    testDir: suite === 'visual' ? './tests/e2e/visual' : './tests/e2e/internal',
    outputDir: `test-results/${suite}`, fullyParallel: false, workers: 1, retries: 0,
    globalSetup: './tests/e2e/support/auth-setup.js',
    reporter: [['list'], ['junit', { outputFile: `reports/e2e-${suite}.xml` }], ['html', { outputFolder: `reports/browser/${suite}/html`, open: 'never' }], ['./tests/e2e/support/evidence-reporter.js', { suite }]],
    use: { baseURL: 'http://127.0.0.1:8018', browserName: 'chromium', storageState: 'playwright/.auth/admin.json', trace: 'retain-on-failure', screenshot: suite === 'visual' ? 'only-on-failure' : 'on', viewport: { width: 1440, height: 1000 } },
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8018', url: 'http://127.0.0.1:8018/login', reuseExistingServer: false, timeout: 60000,
        env: { APP_URL: 'http://127.0.0.1:8018', SESSION_DRIVER: 'file', SESSION_SECURE_COOKIE: 'false', CACHE_STORE: 'array', RUC_API_KEY: '', AGROCLOUD_AI_API_KEY: '', CLOUDINARY_URL: '', MAIL_MAILER: 'array', BROADCAST_CONNECTION: 'null', QUEUE_CONNECTION: 'sync' },
    },
});
