DC := docker compose
PHP := $(DC) run --rm php

.DEFAULT_GOAL := help
.PHONY: help init up down restart build install migrate seed user shell logs test unit e2e stan cs cs-fix check queue

help:
	@grep -E '^[a-z-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

init: build up install migrate seed user ## Полная установка с нуля

up: ## Поднять контейнеры
	$(DC) up -d

down: ## Остановить контейнеры
	$(DC) down

restart: down up ## Перезапустить контейнеры

build: ## Собрать образы
	$(DC) build

install: ## Установить зависимости
	$(PHP) composer install
	npm install

migrate: ## Применить миграции
	$(PHP) php yii migrate --interactive=0

seed: ## Загрузить демонстрационные данные
	$(PHP) php yii seed

user: ## Создать пользователя demo/secret123
	$(PHP) php yii user/create demo secret123

queue: ## Запустить обработчик очереди
	$(PHP) php yii queue/listen

shell: ## Консоль внутри контейнера
	$(PHP) sh

logs: ## Логи контейнеров
	$(DC) logs -f

unit: ## Модульные тесты
	$(PHP) vendor/bin/codecept run Unit

e2e: ## Сквозные тесты
	npx playwright test

stan: ## Статический анализ
	$(PHP) vendor/bin/phpstan analyse --memory-limit=1G

cs: ## Проверка стиля
	$(PHP) vendor/bin/phpcs

cs-fix: ## Автоисправление стиля
	$(PHP) vendor/bin/phpcbf

test: unit e2e ## Все тесты

check: cs stan unit ## Полная проверка перед коммитом
