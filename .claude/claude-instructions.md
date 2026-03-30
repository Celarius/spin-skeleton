# Claude AI Instructions — Spin Skeleton Application

Guidance for Claude Code when working in this application repository.

## What This Project Is

**Spin Skeleton** is a starter application built on `celarius/spin-framework`. It demonstrates framework usage patterns and serves as a base for Spin-based applications.

- **App namespace:** `App\` → `src/app/` (PSR-4)
- **Framework namespace:** `Spin\` → `vendor/celarius/spin-framework/src/`
- **Entry point:** `src/public/bootstrap.php`
- **Config:** `src/app/Config/config-{env}.json`
- **Routes:** `src/app/Config/routes-{env}.json`

## Project Structure

```
src/
  app/
    Classes/
      Managers/
        AbstractManager.php       # Base manager class
        SessionManager.php        # Session management
    Config/
      config-dev.json             # Dev environment config
      config-unittest.json        # Test environment config
      routes-dev.json             # Dev route definitions
      version.json                # App version info
    Controllers/
      AbstractController.php      # App base controller (extends Spin\Core\Controller)
      AbstractPlatesController.php # Base controller for Plates template rendering
      AbstractRestController.php  # Base controller for REST endpoints
      Api/                        # API controllers
        HealthController.php
        InfoController.php
        StatusController.php
        V1/Sessions/
          SessionController.php
      DefaultController.php
      Error4xxController.php      # 4xx error handler
      Error5xxController.php      # 5xx error handler
      ExampleController.php
      IndexController.php
    Middlewares/
      AbstractMiddleware.php      # App base middleware (extends Spin\Core\Middleware)
      AuthHttpBeforeMiddleware.php
      ClientInfoBeforeMiddleware.php
      CorsBeforeMiddleware.php
      RequestIdBeforeMiddleware.php
      RequestIdAfterMiddleware.php
      ResponseLogAfterMiddleware.php
      ResponseTimeAfterMiddleware.php
      SessionBeforeMiddleware.php
      SessionHttpBeforeMiddleware.php
    Views/
      Errors/                     # Error page templates
      Pages/                      # Application page templates
    Globals.php                   # Custom global helper registrations
  public/
    bootstrap.php                 # Application entry point
  storage/
    logs/
    cache/
tests/                            # PHPUnit test suite
doc/                              # Feature documentation
```

## Controller Patterns

### Base hierarchy

```
Spin\Core\Controller
  └── App\Controllers\AbstractController        # Returns 405 for all methods
        ├── App\Controllers\AbstractRestController   # REST helpers
        ├── App\Controllers\AbstractPlatesController # Plates template helpers
        └── (concrete controllers)
```

### Implementing a controller

Only override the HTTP methods your endpoint handles — unimplemented ones return 405 automatically.

```php
declare(strict_types=1);
namespace App\Controllers\Api\V1;

use Psr\Http\Message\ResponseInterface;
use App\Controllers\AbstractController;

class ItemController extends AbstractController
{
    public function handleGET(array $args): ResponseInterface
    {
        try {
            $id = $args['id'] ?? null;
            if (!$id) {
                return responseJsonError(400, 'Missing id');
            }
            // ... fetch data ...
            return responseJson(['id' => $id]);
        } catch (\Throwable $e) {
            logger()->error($e->getMessage(), ['exception' => $e]);
            return responseJsonError(500, 'Internal error');
        }
    }

    public function handlePOST(array $args): ResponseInterface
    {
        try {
            $body = getRequest()->getParsedBody() ?? [];
            // ... create resource ...
            return responseJson($result, 201);
        } catch (\Throwable $e) {
            logger()->error($e->getMessage(), ['exception' => $e]);
            return responseJsonError(500, 'Internal error');
        }
    }
}
```

### Register in routes-{env}.json

```json
{
  "groups": [
    {
      "name": "api-v1",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\CorsBeforeMiddleware"],
      "routes": [
        {
          "methods": ["GET", "POST"],
          "path": "/items",
          "handler": "\\App\\Controllers\\Api\\V1\\ItemController"
        },
        {
          "methods": ["GET", "PUT", "DELETE"],
          "path": "/items/{id}",
          "handler": "\\App\\Controllers\\Api\\V1\\ItemController"
        }
      ]
    }
  ]
}
```

## Middleware Patterns

### Base hierarchy

```
Spin\Core\Middleware
  └── App\Middlewares\AbstractMiddleware
        └── (concrete middleware)
```

### Naming convention

- `*BeforeMiddleware` — runs before the controller (authentication, CORS, logging)
- `*AfterMiddleware` — runs after the controller (response time, audit log)

### Implementing middleware

```php
declare(strict_types=1);
namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;

class MyBeforeMiddleware extends AbstractMiddleware
{
    public function initialize(array $args): bool
    {
        // Called once; read config, prepare state
        return true;
    }

    public function handle(array $args): bool
    {
        // Called per request; return false to stop pipeline
        return true;
    }
}
```

## Globals.php

Register custom application helpers in `src/app/Globals.php`. Always guard with `function_exists()`:

```php
if (!function_exists('myHelper')) {
    function myHelper(): mixed {
        // ...
    }
}
```

## Configuration

Config files live in `src/app/Config/config-{env}.json`. Use `${env:VAR}` macros for secrets. The active environment is set via the `ENVIRONMENT` env variable (defaults to `dev`).

```json
{
  "application": {
    "name": "My App",
    "version": "${env:APP_VERSION:1.0.0}"
  },
  "connections": [
    {
      "name": "db",
      "driver": "mysql",
      "host": "${env:DB_HOST:localhost}",
      "port": 3306,
      "database": "${env:DB_NAME}",
      "username": "${env:DB_USER}",
      "password": "${env:DB_PASS}",
      "charset": "utf8mb4"
    }
  ]
}
```

## Testing

Tests live in `tests/` mirroring `src/app/`. Use PHPUnit 12.

```bash
# Windows
.\phpunit.cmd

# Linux/macOS
./vendor/bin/phpunit
```

## Coding Conventions

1. `declare(strict_types=1);` at the top of every PHP file.
2. Namespace `App\` maps to `src/app/` (PSR-4).
3. Explicit type hints; avoid `mixed` unless unavoidable.
4. Docblocks on all public methods and class-level `@var` annotations.
5. Never define routes in PHP — use `routes-{env}.json`.
6. Never hardcode credentials — use `${env:VAR}` macros.
7. Extend the app's abstract base classes, not the framework classes directly.

## Anti-Patterns

- **Don't extend `Spin\Core\Controller` directly** — extend `App\Controllers\AbstractController` instead.
- **Don't extend `Spin\Core\Middleware` directly** — extend `App\Middlewares\AbstractMiddleware` instead.
- **Don't put SQL in controllers** — extract to a repository class.
- **Don't put complex business logic in controllers** — extract to a service or manager class.
- **Don't edit `vendor/`** — it's read-only; extend via app-level classes.

## Global Helpers Reference

| Helper | Purpose |
|--------|---------|
| `app()` | PSR-11 DI container |
| `config(string $key, $default)` | Read JSON config value |
| `env(string $name, $default)` | Read environment variable |
| `getRequest()` | Current PSR-7 request |
| `getResponse()` | Current PSR-7 response |
| `response(string $body, int $status)` | Plain text response |
| `responseJson($data, int $status)` | JSON response |
| `responseJsonError(int $status, string $msg)` | Standardized error envelope |
| `responseHtml(string $html, int $status)` | HTML response |
| `redirect(string $url, int $status)` | Redirect response |
| `logger()` | PSR-3 Monolog logger |
| `cache(?string $name)` | PSR-16 cache adapter |
| `db(?string $name)` | Named PDO connection |
| `queryParam(string $name, $default)` | GET query parameter |
| `postParam(string $name, $default)` | POST body parameter |
| `cookieParam(string $name, $default)` | Cookie value |
| `generateRefId()` | Unique reference ID (non-sequential) |
| `container()` | Request-scoped value store |
