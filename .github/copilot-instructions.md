# Copilot Instructions for SPIN Skeleton (example app)

Quick, actionable guidance to help an AI coding agent be productive working on the skeleton application that demonstrates `spin-framework` usage.

## Big picture
- **Purpose:** this repository is an example application showing how to wire and use the `spin-framework` library. It is _not_ the framework itself — it demonstrates configuration, routing, controllers, middleware, and view integration.

## Where to look (important files)
- **App bootstrap:** `src/public/bootstrap.php` — application entry point and request dispatch.
- **Application code:** `src/app/` — contains `Globals.php`, `Config/`, `Controllers/`, `Middlewares/`, and `Views/`.
- **Configuration:** JSON files live under `src/app/Config/` (environment-specific files like `config-DEV.json`) and may use `${env:VAR}` macros.
- **Docs:** `doc/request_lifecycle.md` and `doc/template_engines.md` explain app request flow and template integration.

## Typical conventions used by the skeleton
- **Controllers:** app controllers live in `src/app/Controllers/` and follow framework convention of HTTP-verb handlers (e.g., `handleGET`, `handlePOST`). They return framework helper responses like `response()` or `responseJson()`.
- **Middlewares:** app middlewares extend `Spin\\Core\\Middleware` and implement `initialize(array $args): bool` and `handle(array $args): bool`. Middleware classes are referenced by fully-qualified class name in route JSON.
- **JSON routes:** route definitions are usually placed under `src/app/Config/` (the skeleton demonstrates JSON-first routing). Route JSON supports `common`, `groups`, `before`/`after` middleware arrays, `prefix`, and `routes` entries.
- **Globals:** `src/app/Globals.php` contains helper wiring and application-specific global functions used by controllers/middleware.

## Developer workflows & commands (copyable)
- Install dependencies (powerful to run from repo root):
```powershell
composer install
```
- Run the built-in PHP dev server (quick local testing):
```powershell
php -S localhost:8000 -t src/public
```
- Run unit tests (Windows):
```powershell
.\vendor\bin\phpunit.bat
```
- Run unit tests (Linux/macOS):
```bash
./vendor/bin/phpunit
```

## Examples & quick templates
- Method dockblocks:
```php
    /**
     * [description of method]
     *
     * [additional, optiional details]
     *
     * @param <type> <name> <description>
     *
     * @return <type> <description>
     *
     * @throws <ExceptionType> <description>
     */
    public function functionName($params)
    {
    }
```

For arrays add the <mixed> by default, unless it is known what type of array it is:
```php
    /**
     * @param array<mixed> $args Description
     */
```

- Property docblocks:
```php
    /**
     * <description of property>
     * @var <type>
     */
    private <type> $propertyName;
```

- Middleware skeleton:
```php
<?php declare(strict_types=1);
namespace App\\Middlewares;
use Spin\\Core\\Middleware;

class AuthHttpBeforeMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        $this->secret = config('application.secret');
        return true;
    }

    public function handle(array $args): bool
    {
        $token = getRequest()->getHeaderLine('Authorization');
        if (!$this->validateToken($token)) {
            responseJson(['error' => 'Unauthorized'], 401);
            return false;
        }
        return true;
    }
}
```

- Route JSON (group snippet):
```json
{
  "groups": [
    {
      "name": "Public API",
      "prefix": "/api/v1",
      "before": ["\\App\\Middlewares\\CorsBeforeMiddleware"],
      "routes": [
        {"methods": ["GET"], "path": "/health", "handler": "\\App\\Controllers\\Api\\HealthController"}
      ]
    }
  ]
}
```
## Standards
- Follow the PSR-12 coding standard for PHP code.
- Use strict types (`declare(strict_types=1);`) in all PHP files.
- Follow PSR standards where possible
- Follow SOLID principles for class design.
- Use dependency injection for class dependencies where applicable.
- Use type hints and return types for all functions and methods.

## Patterns and anti-patterns (skeleton-specific)
- Use the framework helpers (`config()`, `response()`, `responseJson()`, `getRequest()`) rather than direct PSR-7 instantiation inside app controllers — the skeleton expects these helpers.
- Keep route definitions in JSON config — avoid hard-coding route lists in controller files.

## Tests & CI
- The skeleton includes `phpunit.xml` and example tests in `tests/`. Use the vendor-provided PHPUnit binary for reliable results.

## What not to change without discussion
- Do not change global helper names or signatures in `src/app/Globals.php` without coordination — other app code relies on them.
- Avoid modifying the `src/public/bootstrap.php` request lifecycle unless adding instrumentation or well-justified behavior; changes here affect entire app dispatch.

If you'd like, I can also add a short quick-reference (3–6 one-line snippets) for common tasks (add route, add middleware, run tests). Want that?
