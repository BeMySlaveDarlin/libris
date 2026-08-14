# Libris

Каталог книг на Yii2 Демо — https://libris.bemyslavedarlin.ru, вход `demo` / `secret123`.

## Возможности

- CRUD книг и авторов
- Отчёт ТОП-10 авторов по числу книг за выбранный год, доступен без авторизации
- Подписка неаутентифицированного гостя на автора по номеру телефона
- Отправка SMS через [SMSPilot](https://smspilot.ru/) при добавлении книги подписанного автора

## Стек

PHP 8.4, Yii2, MySQL 8.4, Docker.

## Запуск

```bash
docker compose up -d
docker compose run --rm php composer install
docker compose run --rm php php yii migrate --interactive=0
docker compose run --rm php php yii seed
docker compose run --rm php php yii user/create demo secret123
```

Локально http://localhost:8080, вход `demo` / `secret123`.

Запуск очереди

```bash
docker compose run --rm php php yii queue/listen
```

Без запущенного обработчика задания копятся в таблице `queue` и уходят при следующем запуске.

## Переменные окружения

| Переменная                   | Назначение                 | По умолчанию                     |
|------------------------------|----------------------------|----------------------------------|
| `DB_DSN`                     | строка подключения к MySQL | `mysql:host=mysql;dbname=libris` |
| `DB_USERNAME`, `DB_PASSWORD` | доступ к БД                | `libris` / `libris`              |
| `SMSPILOT_API_KEY`           | ключ SMSPilot              | тестовый ключ-эмулятор           |
| `SMSPILOT_SENDER`            | имя отправителя            | `INFORM`                         |
| `COOKIE_VALIDATION_KEY`      | ключ подписи cookie        | значение для разработки          |

Ключ SMSPilot читается из окружения и в репозиторий не попадает. По умолчанию подставлен ключ-эмулятор из документации
провайдера — реальной отправки не происходит.

## Структура

```
commands/     консольные команды: сидер каталога, создание пользователя
config/       конфигурация веб-приложения, консоли, тестов и DI-контейнера
controllers/  контроллеры: только приём запроса и выбор представления
jobs/         задания очереди
migrations/   миграции схемы и инициализация RBAC
models/       ActiveRecord, формы, поведения, классы запросов
rbac/         имена ролей и разрешений
services/     бизнес-логика: каталог, отчёт, подписки, отправка SMS, хранилище обложек
validators/   валидаторы ISBN и телефона
views/        представления
tests/Unit/   модульные тесты
tests/E2E/    сквозные тесты Playwright
```

## Тесты и качество

```bash
docker compose run --rm php vendor/bin/codecept run Unit
docker compose run --rm php vendor/bin/phpstan analyse
docker compose run --rm php vendor/bin/phpcs
npx playwright test
```

Модульные тесты используют отдельную базу `libris_test`, её нужно создать и мигрировать:

```bash
docker compose exec mysql mysql -uroot -proot -e "CREATE DATABASE libris_test CHARACTER SET utf8mb4; GRANT ALL ON libris_test.* TO 'libris'@'%';"
docker compose run --rm -e DB_DSN='mysql:host=mysql;dbname=libris_test' php php yii migrate --interactive=0
```

Сквозные тесты запускаются против поднятого приложения на `http://localhost:8080`, адрес переопределяется переменной
`E2E_BASE_URL`.

## Решения по реализации

**Повторная отправка SMS.** Каждая пара «подписка — книга» фиксируется в таблице `sms_delivery`
с уникальным индексом. Задание очереди, перезапущенное после сбоя, не отправит гостю второе сообщение о той же книге.

**Отправка SMS за интерфейсом.** `SmsSenderInterface` отделяет логику уведомлений от провайдера:
в тестах подставляется фиктивная реализация, смена провайдера не затрагивает сервисы.

**Телефон и ISBN.** Номер приводится к формату E.164, ISBN — к ISBN-13 с проверкой контрольной суммы. Оба преобразования
выполняются в валидаторах, поэтому в базу попадают только нормализованные значения.
