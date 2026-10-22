import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [['list'], ['junit', { outputFile: 'reports/e2e.xml' }]],
    use: { baseURL: 'http://127.0.0.1:8017', browserName: 'chromium', trace: 'retain-on-failure' },
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8017',
        url: 'http://127.0.0.1:8017/login',
        reuseExistingServer: false,
        env: {
            APP_ENV: 'testing', APP_DEBUG: 'false', APP_URL: 'http://127.0.0.1:8017',
            APP_KEY: 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            SESSION_DRIVER: 'file', SESSION_SECURE_COOKIE: 'false', CACHE_STORE: 'array',
            // Las pruebas públicas no requieren una base de datos ni credenciales.
            DB_CONNECTION: 'sqlite', DB_DATABASE: ':memory:', DB_URL: '',
            MAIL_MAILER: 'array', BROADCAST_CONNECTION: 'null', QUEUE_CONNECTION: 'sync',
        },
    },
});
