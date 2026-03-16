# Configuration Guide

Spin Framework uses JSON-based configuration with environment variable expansion for flexible, environment-specific settings.

## Configuration Files

Configuration files are located in `src/app/Config/` and named by environment:

```
config-dev.json          # Development (ENVIRONMENT=dev)
config-unittest.json     # Testing (ENVIRONMENT=unittest)
config-prod.json         # Production (ENVIRONMENT=prod, create as needed)
routes-dev.json          # API routes (environment-specific)
```

The active configuration is selected by the `ENVIRONMENT` environment variable set in `.env`:

```env
ENVIRONMENT=dev
```

## Environment Variables and .env File

Environment variables are stored in a `.env` file in the project root:

```env
# Application
ENVIRONMENT=dev
APPLICATION_SECRET=my-secret-key-123

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=myapp_db
DB_USERNAME=root
DB_PASSWORD=secret

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379

# Custom vars
API_KEY=abc123xyz789
DEBUG_MODE=true
```

The framework automatically loads variables from `.env` when the application starts. Access them via `${env:VAR_NAME}` syntax in config files (see below).

## Configuration Structure

The main configuration file (`config-{env}.json`) contains these sections:

### application

Global application settings:

```json
"application": {
  "global": {
    "maintenance": false,
    "message": "We are in maintenance mode, back shortly",
    "timezone": "Europe/Stockholm"
  },
  "secret": "${env:APPLICATION_SECRET}"
}
```

| Key | Description |
|-----|-------------|
| `maintenance` | Enable/disable maintenance mode |
| `message` | Message shown when in maintenance mode |
| `timezone` | PHP timezone (e.g., `Europe/Stockholm`, `America/New_York`) |
| `secret` | Application secret key for encryption/hashing |

### session

Session handling configuration:

```json
"session": {
  "cookie": "SID",
  "timeout": 3600,
  "refresh": 600,
  "driver": "apcu",
  "apcu": {
    "option": "value"
  }
}
```

| Key | Description |
|-----|-------------|
| `cookie` | Session cookie name |
| `timeout` | Session timeout in seconds |
| `refresh` | Session refresh interval in seconds |
| `driver` | Session storage driver (`apcu`, `file`, `redis`) |

### logger

Logging configuration:

```json
"logger": {
  "level": "notice",
  "driver": "php",
  "drivers": {
    "php": {
      "line_format": "[%channel%] [%level_name%] %message% %context%",
      "line_datetime": "Y-m-d H:i:s.v e"
    },
    "file": {
      "file_path": "storage/log",
      "file_format": "Y-m-d",
      "line_format": "[%datetime%] [%channel%] [%level_name%] %message% %context%",
      "line_datetime": "Y-m-d H:i:s.v e"
    }
  }
}
```

| Key | Description |
|-----|-------------|
| `level` | Log level (`debug`, `info`, `notice`, `warning`, `error`, `critical`) |
| `driver` | Active logging driver (`php`, `file`, `monolog`) |
| `drivers` | Configuration for each driver |

Available log levels (Monolog):

- `debug` (100) — Detailed debugging information
- `info` (200) — Informational messages
- `notice` (250) — Normal but significant events
- `warning` (300) — Warning conditions
- `error` (400) — Error conditions
- `critical` (500) — Critical conditions

### templates

Template engine configuration:

```json
"templates": {
  "extension": "html",
  "errors": "/Views/Errors",
  "pages": "/Views/Pages"
}
```

| Key | Description |
|-----|-------------|
| `extension` | Template file extension (e.g., `html`, `php`) |
| `errors` | Path to error templates |
| `pages` | Path to page templates |

### caches

Cache adapter definitions:

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
      "port": 6379
    }
  }
}
```

Each cache adapter is registered by key (e.g., `local.apcu`) and configured with:

| Key | Description |
|-----|-------------|
| `adapter` | Display name |
| `class` | Adapter class name |
| `options` | Driver-specific options |

#### APCu Cache

```json
"local.apcu": {
  "adapter": "APCu",
  "class": "\\Spin\\Cache\\Adapters\\Apcu",
  "options": {}
}
```

Requires PHP extension `ext-apcu`.

#### Redis Cache

```json
"remote.redis": {
  "adapter": "Redis",
  "class": "\\Spin\\Cache\\Adapters\\Redis",
  "options": {
    "host": "${env:REDIS_HOST}",
    "port": 6379,
    "password": "${env:REDIS_PASSWORD}",
    "database": 0
  }
}
```

Requires `predis/predis` package.

#### File Cache

```json
"local.file": {
  "adapter": "File",
  "class": "\\Spin\\Cache\\Adapters\\File",
  "options": {
    "path": "storage/cache"
  }
}
```

### connections

Database connection definitions:

```json
"connections": {
  "default": {
    "type": "Pdo",
    "driver": "mysql",
    "schema": "${env:DB_DATABASE}",
    "host": "${env:DB_HOST}",
    "port": "${env:DB_PORT}",
    "username": "${env:DB_USERNAME}",
    "password": "${env:DB_PASSWORD}",
    "charset": "UTF8",
    "options": {
      "ATTR_PERSISTENT": false,
      "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
      "ATTR_AUTOCOMMIT": false,
      "ATTR_EMULATE_PREPARES": false
    }
  }
}
```

See [Database Connections Guide](database-connections.md) for complete database configuration examples.

## Environment Variable Expansion

Configuration values can reference environment variables using `${env:VAR_NAME}` syntax:

```json
{
  "application": {
    "secret": "${env:APPLICATION_SECRET}"
  },
  "connections": {
    "default": {
      "host": "${env:DB_HOST}",
      "port": "${env:DB_PORT}",
      "schema": "${env:DB_DATABASE}",
      "username": "${env:DB_USERNAME}",
      "password": "${env:DB_PASSWORD}"
    }
  }
}
```

The framework automatically expands `${env:VAR_NAME}` references when loading the configuration.

## Loading Config in Code

Access configuration values in controllers, middleware, and other code using the `config()` helper:

```php
// Get entire config section
$appConfig = config('application');

// Get nested value with dot notation
$timezone = config('application.global.timezone');
$secret = config('application.secret');
$dbHost = config('connections.default.host');

// Get with default value
$redisHost = config('caches.remote.redis.options.host', 'localhost');
```

## Secrets Management Best Practices

### Do's

✓ Store secrets in `.env` file (not version controlled)
✓ Use `${env:VAR_NAME}` references in config files
✓ Rotate secrets regularly in production
✓ Use strong, random application secrets
✓ Restrict `.env` file permissions (600)

### Don'ts

✗ Never commit `.env` files to version control
✗ Never hardcode secrets in config files
✗ Never use weak or predictable secrets
✗ Never log sensitive values
✗ Never share secrets in chat or email

### .gitignore

Ensure `.env` is in `.gitignore`:

```
.env
.env.local
*.key
storage/logs/*
storage/cache/*
```

Provide a template for setup:

```
.env.example
```

## Environment-Specific Configs

Use different config files for different environments:

**Development** (`config-dev.json`)
```json
{
  "application": {
    "global": { "maintenance": false, "timezone": "Europe/Stockholm" },
    "secret": "${env:APPLICATION_SECRET}"
  },
  "logger": { "level": "debug", "driver": "php" },
  "connections": { "default": { "host": "localhost", "schema": "${env:DB_DATABASE}" } }
}
```

**Production** (`config-prod.json`)
```json
{
  "application": {
    "global": { "maintenance": false, "timezone": "Europe/Stockholm" },
    "secret": "${env:APPLICATION_SECRET}"
  },
  "logger": { "level": "error", "driver": "file" },
  "connections": { "default": { "host": "${env:DB_HOST}", "schema": "${env:DB_DATABASE}" } }
}
```

**Testing** (`config-unittest.json`)
```json
{
  "application": {
    "global": { "maintenance": false, "timezone": "UTC" },
    "secret": "test-secret"
  },
  "logger": { "level": "debug", "driver": "php" },
  "connections": { "default": { "driver": "sqlite", "filename": ":memory:" } }
}
```

Switch environments with the `ENVIRONMENT` variable:

```bash
export ENVIRONMENT=prod
# or
ENVIRONMENT=prod php -S localhost:8000 -t src/public
```

## Common Configuration Changes

### Enable File-Based Logging

```json
"logger": {
  "level": "notice",
  "driver": "file",
  "drivers": {
    "file": {
      "file_path": "storage/logs",
      "file_format": "Y-m-d",
      "line_format": "[%datetime%] [%channel%] [%level_name%] %message% %context%",
      "line_datetime": "Y-m-d H:i:s.v e"
    }
  }
}
```

### Switch to Redis Cache

```json
"caches": {
  "default": {
    "adapter": "Redis",
    "class": "\\Spin\\Cache\\Adapters\\Redis",
    "options": {
      "host": "${env:REDIS_HOST}",
      "port": 6379,
      "password": "${env:REDIS_PASSWORD}"
    }
  }
}
```

### Enable Maintenance Mode

```json
"application": {
  "global": {
    "maintenance": true,
    "message": "System under maintenance. Back online at 2 PM EST.",
    "timezone": "America/New_York"
  }
}
```

### Add Multiple Database Connections

```json
"connections": {
  "primary": {
    "type": "Pdo",
    "driver": "mysql",
    "host": "${env:DB_PRIMARY_HOST}",
    "schema": "${env:DB_PRIMARY_DB}",
    "username": "${env:DB_PRIMARY_USER}",
    "password": "${env:DB_PRIMARY_PASS}"
  },
  "analytics": {
    "type": "Pdo",
    "driver": "postgresql",
    "host": "${env:ANALYTICS_HOST}",
    "schema": "${env:ANALYTICS_DB}",
    "username": "${env:ANALYTICS_USER}",
    "password": "${env:ANALYTICS_PASS}"
  }
}
```

## See Also

- [Getting Started Guide](getting-started.md)
- [Database Connections Guide](database-connections.md)
- [Controllers & Middleware Guide](controllers-and-middleware.md)
