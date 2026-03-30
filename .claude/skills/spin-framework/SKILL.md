---
name: spin-framework
description: Use when coding or designing applications with the Spin PHP framework (celarius/spin-framework). This is the overview/index — detailed patterns load automatically via the relevant spin-* sub-skill. Covers project structure, bootstrap entry point, and routes you to the right sub-skill for routing, controllers, middleware, config, database, cache, or helpers.
---

# Spin Framework — Overview

Spin is a lightweight PHP 8.3+ MVC framework using PSR-3/7/11/15/16/17 standards. JSON-driven routing, middleware pipeline, PDO connections, PSR-16 cache.
Reference: https://github.com/Celarius/spin-framework (develop branch) · Skeleton: https://github.com/Celarius/spin-skeleton

---

## Project Structure

```
project/
├── src/
│   ├── app/
│   │   ├── Config/
│   │   │   ├── config-dev.json       # App config for "dev" environment
│   │   │   ├── config-prod.json      # App config for "prod" environment
│   │   │   ├── routes-dev.json       # Routes for "dev" environment
│   │   │   ├── routes-prod.json      # Routes for "prod" environment
│   │   │   └── version.json          # App name/code/version
│   │   ├── Controllers/              # HTTP controllers (App\Controllers\)
│   │   ├── Middlewares/              # Middleware classes (App\Middlewares\)
│   │   ├── Repositories/             # Data access layer (App\Repositories\)
│   │   ├── Services/                 # Business logic services
│   │   ├── Classes/                  # Reusable utility classes
│   │   └── Globals.php               # Custom global helper functions
│   ├── public/
│   │   └── bootstrap.php             # Entry point — ALL requests land here
│   └── storage/
│       ├── logs/
│       └── cache/
├── .env                              # ENVIRONMENT and secrets (git-ignored)
├── composer.json
└── vendor/
```

**Environment selection:** `ENVIRONMENT` in `.env` selects `config-{env}.json` and `routes-{env}.json`.

---

## Bootstrap (`src/public/bootstrap.php`)

```php
<?php declare(strict_types=1);
require_once __DIR__.'/../../vendor/autoload.php';

$app = new \Spin\Application(__DIR__.'/..');

if (file_exists($app->getAppPath().'/Globals.php')) {
    require_once $app->getAppPath().'/Globals.php';
}

try {
    if (!$app->run()) {
        if (response()->getStatusCode() == 0) {
            response()->withStatus(404, '');
        }
    }
} catch (\Exception $e) {
    logger()->critical('Global Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
    responseJson(['message' => 'Internal Server Error'], 500);
} finally {
    $app->sendResponse();
}
```

Web server: point document root to `src/public/`. Rewrite all requests to `bootstrap.php`.

---

## Skill Index — Load the Right Skill

| Task | Load skill |
|------|-----------|
| Define or debug routes, 404/405 | `spin-routing` |
| Write or fix a controller | `spin-controllers` |
| Write or fix middleware | `spin-middleware` |
| Configure app / db / cache / logger | `spin-config` |
| Write SQL, repositories, transactions | `spin-database` |
| Add a caching layer | `spin-cache` |
| Use helpers, container, logger, Globals.php | `spin-helpers` |
| Scaffold a SmartBRF controller + test | `new-controller` (project skill) |
| Add a route to SmartBRF routes.json | `add-route` (project skill) |
| Create a database migration | `create-migration` (project skill) |
| Write PHPUnit tests | `phpunit-12` / `create-unit-test` |
| MariaDB multi-tenant query patterns | `mariadb-smartbrf` (project skill) |

---

## Quick Setup

```bash
git clone https://github.com/Celarius/spin-skeleton.git myapp
cd myapp
composer install
cp .env.example .env   # Set ENVIRONMENT=dev, DB_*, APP_SECRET, etc.
php -S localhost:8000 -t src/public
curl http://localhost:8000/api/health
```
