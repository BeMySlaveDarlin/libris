import { expect, test } from '@playwright/test';

const login = async (page) => {
    await page.goto('/site/login');
    await page.getByLabel('Логин').fill('demo');
    await page.getByLabel('Пароль').fill('secret123');
    await page.getByRole('button', { name: 'Войти' }).click();
    await expect(page.locator('.dateline')).toContainText('demo');
};

const unique = () => String(Date.now()).slice(-6);

test.describe('работа под пользователем', () => {
    test.beforeEach(async ({ page }) => await login(page));

    test('после входа появляются кнопки создания', async ({ page }) => {
        await expect(page.locator('.nav-actions').getByText('+ книга')).toBeVisible();
        await expect(page.locator('.nav-actions').getByText('+ автор')).toBeVisible();
    });

    test('книга создаётся с автором и жанром', async ({ page }) => {
        const title = `Тестовая книга ${unique()}`;

        await page.goto('/book/create');
        await page.getByLabel('Название').fill(title);
        await page.getByLabel('Год выпуска').fill('2015');
        await page.selectOption('select[name="BookForm[authorIds][]"]', { index: 0 });
        await page.locator('.chip').first().click();
        await page.getByRole('button', { name: 'Сохранить' }).click();

        await expect(page.locator('main h1')).toHaveText(title);
        await expect(page.locator('.entry-tags .tag').first()).toBeVisible();
    });

    test('новый жанр из формы попадает в справочник', async ({ page }) => {
        const genre = `жанр${unique()}`;

        await page.goto('/book/create');
        await page.getByLabel('Название').fill(`Книга с новым жанром ${unique()}`);
        await page.getByLabel('Год выпуска').fill('2016');
        await page.selectOption('select[name="BookForm[authorIds][]"]', { index: 0 });
        await page.getByLabel('Новые жанры').fill(genre);
        await page.getByRole('button', { name: 'Сохранить' }).click();

        await expect(page.locator('.entry-tags')).toContainText(new RegExp(genre, 'i'));

        await page.goto('/');
        await expect(page.locator('.chips')).toContainText(new RegExp(genre, 'i'));
    });

    test('книга без авторов не сохраняется', async ({ page }) => {
        await page.goto('/book/create');
        await page.getByLabel('Название').fill('Книга без авторов');
        await page.getByLabel('Год выпуска').fill('2015');
        await page.getByRole('button', { name: 'Сохранить' }).click();

        await expect(page.locator('.help-block').filter({ hasText: /Необходимо заполнить/ }).first()).toBeVisible();
    });

    test('некорректный ISBN отклоняется', async ({ page }) => {
        await page.goto('/book/create');
        await page.getByLabel('Название').fill(`ISBN тест ${unique()}`);
        await page.getByLabel('Год выпуска').fill('2015');
        await page.getByLabel('ISBN').fill('978-0-441-47812-4');
        await page.selectOption('select[name="BookForm[authorIds][]"]', { index: 0 });
        await page.getByRole('button', { name: 'Сохранить' }).click();

        await expect(page.getByText('не является корректным ISBN')).toBeVisible();
    });

    test('автор создаётся и открывается', async ({ page }) => {
        const name = `Автор Тестовый ${unique()}`;

        await page.goto('/author/create');
        await page.getByLabel('ФИО').fill(name);
        await page.getByRole('button', { name: 'Сохранить' }).click();

        await expect(page.locator('main h1')).toHaveText(name);
    });

    test('книга удаляется', async ({ page }) => {
        const title = `Удаляемая ${unique()}`;

        await page.goto('/book/create');
        await page.getByLabel('Название').fill(title);
        await page.getByLabel('Год выпуска').fill('2016');
        await page.selectOption('select[name="BookForm[authorIds][]"]', { index: 0 });
        await page.getByRole('button', { name: 'Сохранить' }).click();
        await expect(page.locator('main h1')).toHaveText(title);

        page.on('dialog', (dialog) => dialog.accept());
        await page.getByRole('link', { name: 'Удалить' }).click();

        await expect(page.locator('main h1')).toHaveText('Каталог книг');
    });

    test('выход возвращает к гостевому виду', async ({ page }) => {
        await page.getByRole('button', { name: 'Выход' }).click();

        await expect(page.locator('.dateline')).toContainText('Вход');
        await expect(page.locator('.nav-actions')).toHaveCount(0);
    });
});
