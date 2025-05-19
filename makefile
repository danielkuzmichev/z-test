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

db-test-create:
	$(CONSOLE) doctrine:database:create --env=test --if-not-exists

db-test-drop:
	$(CONSOLE) doctrine:database:drop --env=test --force --if-exists

migrate-test:
	$(CONSOLE) doctrine:migrations:migrate --env=test --no-interaction

fixtures-test:
	docker compose exec app php -d memory_limit=512M bin/console doctrine:fixtures:load --env=test --no-interaction

test-deploy: db-test-create migrate-test fixtures-test

test:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) bin/phpunit

cs-fix:
	$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) ./vendor/bin/php-cs-fixer fix