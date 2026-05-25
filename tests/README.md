# Tests

## Unit tests

Requires PHP 8.2+ on the host, or run inside the Docker PHP container.

**On the host** (from the project root):

```bash
./tests/run.sh
```

The script downloads `phpunit.phar` (PHPUnit 11) on first run if it is not already present.

**Inside the Docker container:**

```bash
docker exec -w /app pk-wdpai-2026-php-1 php tests/phpunit.phar --configuration tests/phpunit.xml
```

## Integration tests

Requires the Docker stack to be running (`docker compose up -d`).

```bash
./tests/integration-tests.sh http://localhost:8080
```

The default base URL is `http://localhost`, so if the app is exposed on port 80 you can omit the argument:

```bash
./tests/integration-tests.sh
```
