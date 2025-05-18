# Запуск 

## Через make

```bash
make deploy
```

## Командами Docker

1. docker compose up -d --build
2. docker compose exec app composer install
3. docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
4. docker compose exec app php -d memory_limit=512M bin/console doctrine:fixtures:load --no-interaction

## UI для проверки API

Приложение можно прверить в ручную через SWAGGER UI http://localhost:8000/api/doc

# Запуск тестов PHPUnit

## Через make

Создание тестовых базы и данных

```bash
make test-deploy
```

Выполнение тестов

```bash
make test
```


