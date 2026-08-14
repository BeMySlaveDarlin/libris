import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './src/tests/E2E',
    fullyParallel: false,
    workers: 1,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? [['github'], ['list']] : [['list']],
    use: {
        baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:25300',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        locale: 'ru-RU',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
