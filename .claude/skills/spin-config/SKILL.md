---
name: spin-config
description: Use when working with Spin PHP application configuration, environment variables, or the bootstrap entry point. Covers config-{env}.json structure (application, logger, caches, connections sections), the ${env:VAR} macro syntax for secrets, config() and env() helper functions, multi-environment setup, the bootstrap.php entry point, and version.json. Always load when adding new config keys, troubleshooting env var resolution, setting up a new environment, or configuring database/cache/logger settings.
---

# Spin Configuration — Reference

---

## Environment Selection

```
ENVIRONMENT=dev   →  config-dev.json  +  routes-dev.json
ENVIRONMENT=prod  →  config-prod.json +  routes-prod.json
```

Set `ENVIRONMENT` in `.env`. Both config and routes files are selected together.

```
src/app/Config/
├── config-dev.json     # Development settings
├── config-prod.json    # Production settings
├── routes-dev.json
├── routes-prod.json
└── version.json        # App name/code/version — used by health endpoint
```

---

## config-{env}.json — Full Structure

```json
{
  "application": {
    "global": {
      "maintenance": false,
      "timezone": "Europe/Stockholm"
    },
    "secret": "${env:APP_SECRET}"
  },
  "logger": {
    "level": "${env:LOG_LEVEL}",
    "driver": "file",
    "drivers": {
      "file": {
        "file_path": "storage/logs",
        "line_format": "[%datetime%] [%channel%] [%level_name%] %message% %context%"
      }
    }
  },
  "caches": {
    "local.apcu": {
      "adapter": "APCu",
      "class": "\\Spin\\Cache\\Adapters\\Apcu",
      "options": {}
    },
    "remote.redis": {
      "adapter": "Redis",
      "class": "\\Spin\\Cache\\Adapters\\Redis",
      "options": {
        "host": "${env:REDIS_HOST}",
        "port": "${env:REDIS_PORT}"
      }
    }
  },
  "connections": {
    "main": {
      "type": "Pdo",
      "driver": "mysql",
      "schema":   "${env:DB_DATABASE}",
      "host":     "${env:DB_HOST}",
      "port":     "${env:DB_PORT}",
      "username": "${env:DB_USERNAME}",
      "password": "${env:DB_PASSWORD}",
      "charset":  "UTF8MB4",
      "options": {
        "ATTR_PERSISTENT":      false,
        "ATTR_ERRMODE":         "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT":      false,
        "ATTR_EMULATE_PREPARES": false
      }
    }
  }
}
```

---

## ${env:VAR} Macro Syntax

JSON values can reference environment variables using `${env:VAR_NAME}`. The macro is resolved at application boot from `.env` or OS environment.

```json
"secret": "${env:APP_SECRET}"
"host":   "${env:DB_HOST}"
"port":   "${env:REDIS_PORT}"
"level":  "${env:LOG_LEVEL}"
```

**Never hardcode credentials directly in config JSON.** The file may be committed to source control.

---

## application Section

| Key | Purpose | Example |
|-----|---------|---------|
| `application.global.maintenance` | Maintenance mode flag | `false` |
| `application.global.timezone` | PHP default timezone | `"Europe/Stockholm"` |
| `application.secret` | App-level secret for signing/encryption | `"${env:APP_SECRET}"` |

---

## logger Section

| Key | Purpose | Values |
|-----|---------|--------|
| `logger.level` | Minimum log level | `debug` / `info` / `notice` / `warning` / `error` / `critical` |
| `logger.driver` | Active driver | `"file"` |
| `logger.drivers.file.file_path` | Log directory path | `"storage/logs"` |
| `logger.drivers.file.line_format` | Log line format (Monolog) | See example above |

Use `${env:LOG_LEVEL}` in dev so you can switch log level via `.env` without editing the JSON. Set `"notice"` or higher in prod config.

---

## caches Section

Named cache adapters. Access via `cache('adapter-name')` in code.

| Adapter name | Class | Use case |
|-------------|-------|---------|
| `local.apcu` | `\\Spin\\Cache\\Adapters\\Apcu` | In-process, per-worker cache (not shared across workers) |
| `remote.redis` | `\\Spin\\Cache\\Adapters\\Redis` | Shared across all workers and instances |

```json
"caches": {
  "local.apcu": {
    "adapter": "APCu",
    "class": "\\Spin\\Cache\\Adapters\\Apcu",
    "options": {}
  },
  "remote.redis": {
    "adapter": "Redis",
    "class": "\\Spin\\Cache\\Adapters\\Redis",
    "options": {
      "host": "${env:REDIS_HOST}",
      "port": "${env:REDIS_PORT}"
    }
  }
}
```

---

## connections Section

Named PDO connections. Access via `db('connection-name')` in code.

```json
"connections": {
  "main": {
    "type": "Pdo",
    "driver": "mysql",
    "schema":   "${env:DB_DATABASE}",
    "host":     "${env:DB_HOST}",
    "port":     "${env:DB_PORT}",
    "username": "${env:DB_USERNAME}",
    "password": "${env:DB_PASSWORD}",
    "charset":  "UTF8MB4",
    "options": {
      "ATTR_PERSISTENT":       false,
      "ATTR_ERRMODE":          "ERRMODE_EXCEPTION",
      "ATTR_AUTOCOMMIT":       false,
      "ATTR_EMULATE_PREPARES": false
    }
  }
}
```

**charset must be `UTF8MB4`** — not `UTF8`. MySQL's `utf8` is limited to 3 bytes (no emoji, no 4-byte Unicode).

| PDO Option | Recommended value | Reason |
|-----------|-----------------|--------|
| `ATTR_PERSISTENT` | `false` | Avoid connection leaks in PHP-FPM |
| `ATTR_ERRMODE` | `ERRMODE_EXCEPTION` | Throw `PDOException` on errors |
| `ATTR_AUTOCOMMIT` | `false` | Explicit transaction control |
| `ATTR_EMULATE_PREPARES` | `false` | True prepared statements (security + performance) |

---

## config() Helper

Read and write configuration values using dot-notation:

```php
// Read
$timezone = config('application.global.timezone');  // "Europe/Stockholm"
$secret   = config('application.secret');           // value of APP_SECRET env var
$logLevel = config('logger.level');                 // "debug"

// Write (runtime only — does not persist to JSON)
config('application.global.maintenance', true);
```

---

## env() Helper

Read environment variables with an optional default:

```php
$host    = env('DB_HOST', 'localhost');  // fallback if not set
$logLevel = env('LOG_LEVEL', 'notice');
$secret  = env('APP_SECRET');           // null if not set
```

Reads from OS environment and `.env` file (loaded at boot).

---

## bootstrap.php Entry Point

```php
<?php declare(strict_types=1);
require_once __DIR__.'/../../vendor/autoload.php';

$app = new \Spin\Application(__DIR__.'/..');  // sets app root path

// Load custom global helpers
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

**Web server config:** Document root → `src/public/`. All requests rewrite to `bootstrap.php`.

---

## version.json

```json
{
  "name":    "App Name",
  "code":    "app-name-api",
  "version": "1.0.0"
}
```

Read by the health and readiness controllers. Update version when deploying releases.

---

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| Hardcoding credentials in JSON | Use `${env:VAR}` macros — config JSON may be committed to git |
| `"charset": "UTF8"` | Must be `"UTF8MB4"` — MySQL's utf8 is limited to 3 bytes |
| Wrong env macro syntax | Must be `${env:VAR_NAME}` (not `$VAR`, not `%VAR%`) |
| Setting `LOG_LEVEL=debug` in prod config | Use `warning` or higher in prod; debug level is very noisy |
| Forgetting to create prod config | Both `config-dev.json` and `config-prod.json` must exist |
| Mixing up connection name | `db('main')` maps to key `"main"` in `connections` — names must match |
| `ATTR_AUTOCOMMIT: true` | Keep `false` — explicit `beginTransaction()`/`commit()` is safer |
