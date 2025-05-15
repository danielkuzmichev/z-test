DOCKER_COMPOSE := docker compose
PHP_CONTAINER := app
CONSOLE := $(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php bin/console

up:
	$(DOCKER_COMPOSE) up -d --build

down:
	$(DOCKER_COMPOSE) down

install:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer install

migrate:
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

fixtures:
	docker compose exec app php -d memory_limit=512M bin/console doctrine:fixtures:load --no-interaction

deploy: up install migrate fixtures
