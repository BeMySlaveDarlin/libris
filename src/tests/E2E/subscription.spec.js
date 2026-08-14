import { expect, test } from '@playwright/test';

const uniquePhone = () => '+7900' + String(Date.now()).slice(-7);

test.describe('подписка гостя', () => {
    test('валидный номер принимается', async ({ page }) => {
        await page.goto('/author/index');
        await page.locator('.card-link').first().click();

        await page.getByLabel('Номер телефона').fill(uniquePhone());
        await page.getByRole('button', { name: 'Подписаться' }).click();

        await expect(page.locator('.flash')).toContainText('Подписка оформлена');
    });

    test('номер в местном формате нормализуется', async ({ page }) => {
        await page.goto('/author/index');
        await page.locator('.card-link').nth(1).click();

        await page.getByLabel('Номер телефона').fill('8 (901) 765-43-21');
        await page.getByRole('button', { name: 'Подписаться' }).click();

        await expect(page.locator('.flash')).toContainText('Подписка оформлена');
    });

    test('мусор вместо номера отклоняется', async ({ page }) => {
        await page.goto('/author/index');
        await page.locator('.card-link').first().click();

        await page.getByLabel('Номер телефона').fill('не телефон');
        await page.getByRole('button', { name: 'Подписаться' }).click();

        await expect(page.locator('.flash-error')).toBeVisible();
    });

    test('на странице книги подписки нет, только отсылка к автору', async ({ page }) => {
        await page.goto('/');
        await page.locator('.card-link').first().click();

        await expect(page.getByRole('button', { name: 'Подписаться' })).toHaveCount(0);
        await expect(page.locator('.hint')).toContainText('на его странице');
    });
});
