import { expect, test } from '@playwright/test';

test.describe('каталог', () => {
    test('главная показывает карточки книг', async ({ page }) => {
        await page.goto('/');

        await expect(page.locator('.masthead h1')).toContainText('Libris');
        await expect(page.locator('.summary')).toContainText('Найдено книг');

        const first = page.locator('.card').first();
        await expect(first.locator('.card-title')).not.toBeEmpty();
        await expect(first.locator('.card-meta')).toContainText(/\d{4}/);
    });

    test('карточка книги открывается кликом', async ({ page }) => {
        await page.goto('/');
        const title = await page.locator('.card-title').first().innerText();
        await page.locator('.card-link').first().click();

        await expect(page.locator('main h1')).toHaveText(title);
        await expect(page.getByText('Год выпуска')).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Описание' })).toBeVisible();
    });

    test('поиск сужает выдачу', async ({ page }) => {
        await page.goto('/');
        const total = await page.locator('.summary').innerText();

        await page.getByLabel('Поиск').fill('the');
        await page.getByRole('button', { name: 'Найти' }).click();

        await expect(page.locator('.summary')).not.toHaveText(total);
        await expect(page.getByRole('link', { name: 'Сбросить' })).toBeVisible();
    });

    test('фильтр по жанру оставляет книги этого жанра', async ({ page }) => {
        await page.goto('/');
        const genre = await page.locator('.chip span').first().innerText();
        await page.locator('.chip').first().click();
        await page.getByRole('button', { name: 'Найти' }).click();

        const tags = page.locator('.card').first().locator('.tag');
        await expect(tags.filter({ hasText: genre })).toHaveCount(1);
    });

    test('сортировка по умолчанию алфавитная и переключается', async ({ page }) => {
        await page.goto('/');
        await expect(page.locator('select[name="sort"]')).toHaveValue('title');

        await page.selectOption('select[name="sort"]', '-year');
        await expect(page).toHaveURL(/sort=-year/);
    });

    test('пагинация ведёт на вторую страницу', async ({ page }) => {
        await page.goto('/');
        await page.getByRole('link', { name: '2', exact: true }).first().click();

        await expect(page).toHaveURL(/page=2/);
        await expect(page.locator('.card').first()).toBeVisible();
    });

    test('мусор в параметрах не ломает страницу', async ({ page }) => {
        await page.goto('/?sort=hack&genres=abc&page=-3');

        await expect(page.locator('main h1')).toHaveText('Каталог книг');
        await expect(page.locator('.card').first()).toBeVisible();
    });
});

test.describe('авторы', () => {
    test('список показывает карточки с профилем', async ({ page }) => {
        await page.goto('/author/index');

        await expect(page.locator('.summary')).toContainText('Найдено авторов');
        await expect(page.locator('.card-person').first().locator('.card-title')).not.toBeEmpty();
    });

    test('страница автора показывает книги и форму подписки', async ({ page }) => {
        await page.goto('/author/index');
        await page.locator('.card-link').first().click();

        await expect(page.getByRole('heading', { level: 2, name: 'Книги' })).toBeVisible();
        await expect(page.getByRole('heading', { level: 2, name: 'Подписаться на новинки' })).toBeVisible();
    });
});

test.describe('права доступа', () => {
    test('гость не видит кнопок создания', async ({ page }) => {
        await page.goto('/');

        await expect(page.locator('.nav-actions')).toHaveCount(0);
    });

    test('гостя перекидывает на вход при попытке создать книгу', async ({ page }) => {
        await page.goto('/book/create');

        await expect(page).toHaveURL(/site\/login/);
        await expect(page.getByLabel('Логин')).toBeVisible();
    });

    test('несуществующая книга отдаёт 404', async ({ page }) => {
        const response = await page.goto('/book/view/999999');

        expect(response?.status()).toBe(404);
    });
});
