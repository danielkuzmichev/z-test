DOCKER_COMPOSE := docker compose
PHP_CONTAINER := app
CONSOLE := $(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/console

up: ## Сборка и запуск контейнеров
	$(DOCKER_COMPOSE) up -d --build

down: ## Остановка контейнеров
	$(DOCKER_COMPOSE) down

install: ## Установка зависимостей
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer install

migrate: ## Применение миграций
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

fixtures: ## Загрузка фикстур
	$(CONSOLE) doctrine:fixtures:load --no-interaction

deploy: up install migrate fixtures ## Полный деплой

## -------- Тесты --------

migrate-test: ## Миграции в тестовую БД
	$(DOCKER_COMPOSE) exec -e APP_ENV=test $(PHP_CONTAINER) php bin/console doctrine:migrations:migrate --no-interaction

fixtures-test: ## Фикстуры для тестов
	$(DOCKER_COMPOSE) exec -e APP_ENV=test $(PHP_CONTAINER) php bin/console doctrine:fixtures:load --no-interaction

test: migrate-test fixtures-test ## Запуск тестов
	$(DOCKER_COMPOSE) exec -e APP_ENV=test $(PHP_CONTAINER) php bin/phpunit
