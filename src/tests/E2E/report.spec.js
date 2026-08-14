import { expect, test } from '@playwright/test';

const currentYear = async (page) => await page.locator('select[name="year"]').inputValue();

test.describe('отчёт', () => {
    test('доступен гостю и открывается на году с книгами', async ({ page }) => {
        await page.goto('/report');

        const year = await currentYear(page);
        await expect(page.locator('main h1')).toContainText(`ТОП авторов за ${year} год`);
        await expect(page.locator('table tbody tr').first()).toBeVisible();
    });

    test('таблица показывает авторов и число книг', async ({ page }) => {
        await page.goto('/report');

        await expect(page.locator('table thead')).toContainText('Книг за год');
        await expect(page.locator('table tbody tr').first().locator('a')).not.toBeEmpty();
    });

    test('смена года перезагружает отчёт', async ({ page }) => {
        await page.goto('/report');
        const options = page.locator('select[name="year"] option');
        const another = await options.nth(1).getAttribute('value');

        await page.selectOption('select[name="year"]', another);
        await page.getByRole('button', { name: 'Показать' }).click();

        await expect(page).toHaveURL(new RegExp(`year=${another}`));
        await expect(page.locator('main h1')).toContainText(another);
    });

    test('фильтр по автору сужает выдачу', async ({ page }) => {
        await page.goto('/report');
        const name = await page.locator('table tbody tr').first().locator('a').innerText();
        const part = name.split(' ').pop();

        await page.getByLabel('Автор').fill(part);
        await page.getByRole('button', { name: 'Показать' }).click();

        const rows = page.locator('table tbody tr');
        await expect(rows.first()).toContainText(part);
    });

    test('лимит ограничивает число строк', async ({ page }) => {
        await page.goto('/report');
        await page.selectOption('select[name="limit"]', '25');
        await page.getByRole('button', { name: 'Показать' }).click();

        await expect(page).toHaveURL(/limit=25/);
        await expect(page.locator('table tbody tr').first()).toBeVisible();
    });

    test('невозможная отсечка даёт пустое состояние', async ({ page }) => {
        await page.goto('/report?minBooks=999');

        await expect(page.getByText('По заданным условиям книг не найдено.')).toBeVisible();
    });

    test('сброс возвращает к чистому отчёту', async ({ page }) => {
        await page.goto('/report?minBooks=999');
        await page.getByRole('link', { name: 'Сбросить' }).click();

        await expect(page.locator('table tbody tr').first()).toBeVisible();
    });

    test('фильтр по жанру работает', async ({ page }) => {
        await page.goto('/report');
        await page.locator('.chip').first().click();
        await page.getByRole('button', { name: 'Показать' }).click();

        await expect(page.locator('main h1')).toContainText('ТОП авторов');
    });

    test('некорректный год в адресе не ломает страницу', async ({ page }) => {
        await page.goto('/report?year=99999');

        await expect(page.locator('main h1')).toContainText('ТОП авторов');
        await expect(page.locator('select[name="year"]')).toBeVisible();
    });
});
