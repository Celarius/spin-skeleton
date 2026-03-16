# Spin-Skeleton Documentation & Standards Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rewrite spin-skeleton documentation for new developers and align codebase with SPIN Framework standards.

**Architecture:**
- Audit all PHP files for `declare(strict_types=1);` and docblock compliance
- Write 5 practical guides (getting-started, configuration, database, controllers, testing)
- Rewrite README to be modern and action-focused
- Apply standards fixes with individual commits
- Compile improvement suggestions into IMPROVEMENTS.md for review

**Tech Stack:** Markdown (guides), PHP (code fixes), PHPUnit (verification)

---

## Chunk 1: Audit & Code Standards Assessment

### Task 1: Audit PHP Files for Standards Compliance

**Files:**
- Inspect: `src/app/**/*.php`
- Reference: SPIN Framework CLAUDE.md standards

- [ ] **Step 1: List all PHP files in src/app/**

```bash
cd c:\Data\Repos\Celarius\github\spin-skeleton
find src/app -type f -name "*.php" -exec basename {} \; | sort
```

Expected output: List of ~25-30 PHP files (Controllers, Middleware, Classes, Globals)

- [ ] **Step 2: Check which files are missing `declare(strict_types=1);`**

For each PHP file in `src/app/`, verify:
- Line 1 should be: `<?php`
- Line 2 should be: `declare(strict_types=1);`

Create a list of files needing this added.

Example files to check:
- `src/app/Globals.php`
- `src/app/Controllers/AbstractPlatesController.php`
- `src/app/Controllers/AbstractRestController.php`
- `src/app/Controllers/Api/HealthController.php`
- `src/app/Controllers/DefaultController.php`
- `src/app/Middlewares/*.php`
- `src/app/Classes/Managers/*.php`

- [ ] **Step 3: Check docblock completeness**

For each file, verify:
- All public methods have `@param` and `@return` docblocks
- Class-level docblock exists
- Properties have `@var` type annotations

Create a list of files needing docblock additions.

- [ ] **Step 4: Verify PSR-4 namespace compliance**

For each file in `src/app/`, check:
- Namespace `App\<Subpath>` matches directory structure
- Example: `src/app/Controllers/Api/HealthController.php` → `namespace App\Controllers\Api;`

Create a list of any mismatches (expect: none, but verify).

- [ ] **Step 5: Commit audit findings**

Create a summary file (not committed, for reference):
```
AUDIT_FINDINGS.txt
==================

Missing declare(strict_types=1):
- src/app/Globals.php
- [list others found]

Missing/incomplete docblocks:
- [files with issues]

PSR-4 violations:
- [any mismatches]
```

---

## Chunk 2: Write Documentation Guides

### Task 2: Write getting-started.md

**Files:**
- Create: `doc/getting-started.md`

- [ ] **Step 1: Write getting-started.md**

```markdown
# Getting Started with Spin-Skeleton

## Prerequisites

- PHP 8.0 or higher
- Composer
- Git
- Optional: Docker & Docker Compose (for isolated environment)

## 5-Minute Setup

### 1. Clone and Install

\`\`\`bash
git clone https://github.com/Celarius/spin-skeleton.git my-app
cd my-app
rm -rf .git
git init
composer install
\`\`\`

### 2. Configure Environment

Copy the example config:

\`\`\`bash
cp .env.example .env
\`\`\`

Edit `.env` and set:
\`\`\`
APPLICATION_SECRET=your-secret-key-here
ENVIRONMENT=dev
\`\`\`

### 3. Run the Application

Using PHP's built-in dev server:

\`\`\`bash
php -S localhost:8000 -t src/public src/public/bootstrap.php
\`\`\`

Visit `http://localhost:8000` in your browser. You should see the index page.

### 4. Verify the API Works

\`\`\`bash
curl http://localhost:8000/api/health
\`\`\`

Expected response:
\`\`\`json
{"status":"ok"}
\`\`\`

### 5. Run Tests

\`\`\`bash
composer test
\`\`\`

Expected: All tests pass ✓

## Project Structure

\`\`\`
my-app/
├── src/
│   ├── app/                          # Your application code
│   │   ├── Classes/                  # Business logic, managers
│   │   ├── Controllers/              # HTTP handlers
│   │   ├── Middlewares/              # Request/response processing
│   │   ├── Views/                    # HTML templates (Plates)
│   │   ├── Config/
│   │   │   ├── config-dev.json       # Environment-specific config
│   │   │   ├── routes-dev.json       # Route definitions
│   │   │   └── version.json          # App version
│   │   └── Globals.php               # App-specific helper functions
│   ├── public/
│   │   ├── bootstrap.php             # Entry point
│   │   └── .htaccess                 # Apache rewrite rules
│   └── storage/
│       ├── logs/                     # Application logs
│       └── cache/                    # Cache files
├── tests/                            # PHPUnit tests
├── doc/                              # Documentation
├── composer.json
└── phpunit.xml
\`\`\`

## What's Included

✓ REST API example (`/api/health`, `/api/status`, `/api/info`)
✓ Web page example (`/`)
✓ Session management
✓ Request/response middleware pipeline
✓ Error handling (4xx, 5xx)
✓ Logging (Monolog)
✓ Caching (APCu, Redis examples)
✓ Database connections (MySQL, SQLite, Firebird examples)
✓ PHPUnit test suite

## Next Steps

1. **Configure your project:** See [Configuration Guide](./configuration.md)
2. **Add database:** See [Database Connections](./database-connections.md)
3. **Create your first controller:** See [Controllers & Middleware](./controllers-and-middleware.md)
4. **Add tests:** See [Testing Guide](./testing.md)

## Troubleshooting

**Port 8000 already in use?**

Use a different port:
\`\`\`bash
php -S localhost:8001 -t src/public src/public/bootstrap.php
\`\`\`

**Composer requires updates?**

Run:
\`\`\`bash
composer update
\`\`\`

**Tests fail?**

Verify environment:
\`\`\`bash
php --version          # Should be 8.0+
composer --version     # Should be recent version
\`\`\`

Then run tests with verbose output:
\`\`\`bash
composer test -- -v
\`\`\`

## Using Docker (Optional)

Create `Dockerfile`:

\`\`\`dockerfile
FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \\
    git \\
    curl \\
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN composer install

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "src/public", "src/public/bootstrap.php"]
\`\`\`

Build and run:
\`\`\`bash
docker build -t my-app .
docker run -p 8000:8000 my-app
\`\`\`

## Learning More

- [SPIN Framework Documentation](https://github.com/Celarius/spin-framework)
- [Request Lifecycle](./request-lifecycle.md)
- [Template Engines](./template-engines.md)
\`\`\`

- [ ] **Step 2: Verify getting-started.md is readable and accurate**

Read through the guide and verify:
- Commands are correct
- Output examples match what users will see
- Links reference existing docs
- No typos

- [ ] **Step 3: Commit**

```bash
git add doc/getting-started.md
git commit -m "docs: add getting-started guide"
```

---

### Task 3: Write configuration.md

**Files:**
- Create: `doc/configuration.md`

- [ ] **Step 1: Write configuration.md**

```markdown
# Configuration Guide

Spin-Skeleton uses JSON-based configuration with environment variable expansion.

## Configuration Files

Configuration is split by environment:

\`\`\`
src/app/Config/
├── config-dev.json        # Development environment
├── config-unittest.json    # Unit testing
├── config-prod.json        # Production (not included, create as needed)
├── routes-dev.json         # Route definitions
└── version.json            # App version
\`\`\`

The active config file is determined by the `ENVIRONMENT` variable (default: `dev`).

## Environment Variables

Set in `.env` file or system environment:

\`\`\`bash
ENVIRONMENT=dev                    # dev, unittest, prod
APPLICATION_SECRET=your-secret     # Used for encryption/signing
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=user
DB_PASSWORD=pass
REDIS_HOST=localhost
REDIS_PORT=6379
\`\`\`

## Configuration Structure

### application

\`\`\`json
{
  "application": {
    "global": {
      "maintenance": false,
      "message": "We are in maintenance mode",
      "timezone": "Europe/Stockholm"
    },
    "secret": "\${env:APPLICATION_SECRET}"
  }
}
\`\`\`

- `maintenance` — Set to true to enable maintenance mode (shows message, blocks requests)
- `message` — Message shown in maintenance mode
- `timezone` — PHP default timezone
- `secret` — Encryption/signing key (required, set via env var)

### session

\`\`\`json
{
  "session": {
    "cookie": "SID",
    "timeout": 3600,
    "refresh": 600,
    "driver": "apcu",
    "apcu": {}
  }
}
\`\`\`

- `cookie` — Session cookie name
- `timeout` — Session timeout in seconds (1 hour default)
- `refresh` — Refresh timeout in seconds (10 minutes default)
- `driver` — Session storage driver (apcu, redis, file)

### logger

\`\`\`json
{
  "logger": {
    "level": "notice",
    "driver": "php",
    "drivers": {
      "php": {
        "line_format": "[%channel%] [%level_name%] %message%",
        "line_datetime": "Y-m-d H:i:s.v e"
      },
      "file": {
        "file_path": "storage/logs",
        "file_format": "Y-m-d",
        "line_format": "[%datetime%] [%level_name%] %message%"
      }
    }
  }
}
\`\`\`

Levels: debug, info, notice, warning, error, critical, alert, emergency

### templates

\`\`\`json
{
  "templates": {
    "extension": "html",
    "errors": "/Views/Errors",
    "pages": "/Views/Pages"
  }
}
\`\`\`

Template locations and file extension.

### caches

\`\`\`json
{
  "caches": {
    "local.apcu": {
      "adapter": "APCu",
      "class": "\\\\Spin\\\\Cache\\\\Adapters\\\\Apcu"
    },
    "remote.redis": {
      "adapter": "Redis",
      "class": "\\\\Spin\\\\Cache\\\\Adapters\\\\Redis",
      "options": {
        "host": "\${env:REDIS_HOST}",
        "port": 6379
      }
    }
  }
}
\`\`\`

Define multiple cache adapters. Use `cache('local.apcu')` to access.

### connections

Database connection definitions:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "\${env:DB_DATABASE}",
      "host": "\${env:DB_HOST}",
      "port": "\${env:DB_PORT}",
      "username": "\${env:DB_USERNAME}",
      "password": "\${env:DB_PASSWORD}",
      "charset": "UTF8"
    }
  }
}
\`\`\`

See [Database Connections](./database-connections.md) for details.

## Environment Variable Expansion

Config files support macro expansion:

\`\`\`json
{
  "database": {
    "host": "\${env:DB_HOST}",
    "password": "\${env:DB_PASSWORD}"
  }
}
\`\`\`

The `\${env:VARIABLE_NAME}` syntax is replaced with environment variable values at runtime.

## Loading Configuration

In your code:

\`\`\`php
\$config = config();  // Load all config
\$secret = config('application.secret');  // Access nested values
\$timezone = config('application.global.timezone');
\`\`\`

## Secrets Management

**Never commit `.env` files with real secrets.**

Best practice:

\`\`\`bash
# Create .env.example with placeholder values
cp .env .env.example
sed -i 's/=.*/=YOUR_VALUE/' .env.example

# Add to .gitignore
echo ".env" >> .gitignore

# In CI/deployment, set env vars before running app
export APPLICATION_SECRET=actual-secret
export DB_PASSWORD=actual-password
php -S localhost:8000 ...
\`\`\`

## Environment-Specific Configs

Create separate config files per environment:

\`\`\`
config-dev.json       # Development (verbose logging, caching disabled)
config-unittest.json  # Testing (SQLite in-memory, no external services)
config-prod.json      # Production (optimized, external Redis, etc.)
\`\`\`

Switch with `ENVIRONMENT` variable:

\`\`\`bash
ENVIRONMENT=prod php -S localhost:8000 ...
\`\`\`

## Common Configuration Changes

### Enable Redis Caching

In config, set:

\`\`\`json
{
  "session": {
    "driver": "redis"
  },
  "caches": {
    "remote.redis": {
      "options": {
        "host": "localhost",
        "port": 6379
      }
    }
  }
}
\`\`\`

Environment:

\`\`\`bash
export REDIS_HOST=localhost
export REDIS_PORT=6379
\`\`\`

### Switch to File-Based Logging

\`\`\`json
{
  "logger": {
    "driver": "file",
    "drivers": {
      "file": {
        "file_path": "storage/logs",
        "file_format": "Y-m-d"
      }
    }
  }
}
\`\`\`

Logs will write to `storage/logs/Y-m-d.log`.

### Disable Maintenance Mode

\`\`\`json
{
  "application": {
    "global": {
      "maintenance": false
    }
  }
}
\`\`\`

## See Also

- [Getting Started](./getting-started.md)
- [Database Connections](./database-connections.md)
\`\`\`

- [ ] **Step 2: Commit**

```bash
git add doc/configuration.md
git commit -m "docs: add configuration guide"
```

---

### Task 4: Write database-connections.md

**Files:**
- Create: `doc/database-connections.md`

- [ ] **Step 1: Write database-connections.md**

```markdown
# Database Connections Guide

Spin-Skeleton uses PDO (PHP Data Objects) for database abstraction. Multiple databases can be configured simultaneously.

## Supported Databases

- MySQL 5.7+
- PostgreSQL 10+
- SQLite 3
- Firebird 3+
- CockroachDB (PostgreSQL-compatible)
- ODBC

## Connection Configuration

Connections are defined in `config-{env}.json`:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "\${env:DB_DATABASE}",
      "host": "\${env:DB_HOST}",
      "port": "\${env:DB_PORT}",
      "username": "\${env:DB_USERNAME}",
      "password": "\${env:DB_PASSWORD}",
      "charset": "UTF8",
      "options": {
        "ATTR_PERSISTENT": false,
        "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT": false
      }
    }
  }
}
\`\`\`

## Setting Up MySQL

### 1. Create Database and User

\`\`\`sql
CREATE DATABASE myapp;
CREATE USER 'myapp_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON myapp.* TO 'myapp_user'@'localhost';
FLUSH PRIVILEGES;
\`\`\`

### 2. Configure Connection

In `.env`:

\`\`\`bash
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=myapp_user
DB_PASSWORD=secure_password
\`\`\`

In `config-dev.json`:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "\${env:DB_DATABASE}",
      "host": "\${env:DB_HOST}",
      "port": "\${env:DB_PORT}",
      "username": "\${env:DB_USERNAME}",
      "password": "\${env:DB_PASSWORD}",
      "charset": "UTF8"
    }
  }
}
\`\`\`

### 3. Use Connection in Code

\`\`\`php
\$db = app()->connections()->connection('default');

// Execute query
\$stmt = \$db->query("SELECT * FROM users WHERE id = ?");
\$result = \$stmt->fetch(PDO::FETCH_ASSOC);
\`\`\`

## Setting Up SQLite (Development)

SQLite is ideal for local development — no server required, data in a single file.

### 1. Configure Connection

In `config-unittest.json`:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "sqlite",
      "filename": ":memory:"
    }
  }
}
\`\`\`

Or use a file:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "sqlite",
      "filename": "storage/database/app.sqlite"
    }
  }
}
\`\`\`

### 2. Verify Connection

\`\`\`bash
ENVIRONMENT=unittest php -S localhost:8000 -t src/public src/public/bootstrap.php
\`\`\`

## Setting Up PostgreSQL

### 1. Create Database and User

\`\`\`sql
CREATE USER myapp_user WITH PASSWORD 'secure_password';
CREATE DATABASE myapp OWNER myapp_user;
\`\`\`

### 2. Configure Connection

In `.env`:

\`\`\`bash
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=myapp
DB_USERNAME=myapp_user
DB_PASSWORD=secure_password
\`\`\`

In `config-prod.json`:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "pgsql",
      "schema": "\${env:DB_DATABASE}",
      "host": "\${env:DB_HOST}",
      "port": "\${env:DB_PORT}",
      "username": "\${env:DB_USERNAME}",
      "password": "\${env:DB_PASSWORD}"
    }
  }
}
\`\`\`

## Multiple Connections

Define multiple connections and access them by name:

\`\`\`json
{
  "connections": {
    "default": { ... },
    "analytics": {
      "type": "Pdo",
      "driver": "mysql",
      "host": "analytics-server.example.com",
      ...
    },
    "reporting": {
      "type": "Pdo",
      "driver": "pgsql",
      "host": "reporting-server.example.com",
      ...
    }
  }
}
\`\`\`

In code:

\`\`\`php
\$default = app()->connections()->connection('default');
\$analytics = app()->connections()->connection('analytics');
\$reporting = app()->connections()->connection('reporting');
\`\`\`

## PDO Options

Common options in connection config:

| Option | Values | Default | Notes |
|--------|--------|---------|-------|
| ATTR_ERRMODE | ERRMODE_SILENT, ERRMODE_WARNING, ERRMODE_EXCEPTION | ERRMODE_SILENT | Recommended: ERRMODE_EXCEPTION |
| ATTR_PERSISTENT | true/false | false | Persistent connections (keep open) |
| ATTR_AUTOCOMMIT | true/false | true | Auto-commit transactions |
| ATTR_EMULATE_PREPARES | true/false | false | Emulate prepared statements |
| ATTR_DEFAULT_FETCH_MODE | PDO::FETCH_* | FETCH_BOTH | Default result format |

Example with all options:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "mysql",
      "host": "localhost",
      "options": {
        "ATTR_PERSISTENT": false,
        "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT": false,
        "ATTR_EMULATE_PREPARES": false,
        "ATTR_DEFAULT_FETCH_MODE": "FETCH_ASSOC"
      }
    }
  }
}
\`\`\`

## Executing Queries

### SELECT

\`\`\`php
\$db = app()->connections()->connection('default');
\$stmt = \$db->query("SELECT * FROM users");
\$users = \$stmt->fetchAll(PDO::FETCH_ASSOC);
\`\`\`

### INSERT

\`\`\`php
\$stmt = \$db->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
\$stmt->execute(['John Doe', 'john@example.com']);
\$lastId = \$db->lastInsertId();
\`\`\`

### UPDATE

\`\`\`php
\$stmt = \$db->prepare("UPDATE users SET email = ? WHERE id = ?");
\$stmt->execute(['new@example.com', 1]);
\$rowsAffected = \$stmt->rowCount();
\`\`\`

### DELETE

\`\`\`php
\$stmt = \$db->prepare("DELETE FROM users WHERE id = ?");
\$stmt->execute([1]);
\`\`\`

### Transactions

\`\`\`php
\$db->beginTransaction();
try {
    \$db->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?")->execute([100, 1]);
    \$db->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([100, 2]);
    \$db->commit();
} catch (Exception \$e) {
    \$db->rollBack();
    throw \$e;
}
\`\`\`

## Testing with SQLite

For unit tests, use in-memory SQLite:

In `config-unittest.json`:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "sqlite",
      "filename": ":memory:"
    }
  }
}
\`\`\`

In test setup, create schema:

\`\`\`php
protected function setUp(): void {
    \$db = app()->connections()->connection('default');
    \$db->exec(file_get_contents('tests/fixtures/schema.sql'));
}
\`\`\`

## See Also

- [Getting Started](./getting-started.md)
- [Configuration Guide](./configuration.md)
- [Testing Guide](./testing.md)
\`\`\`

- [ ] **Step 2: Commit**

```bash
git add doc/database-connections.md
git commit -m "docs: add database connections guide"
```

---

### Task 5: Write controllers-and-middleware.md

**Files:**
- Create: `doc/controllers-and-middleware.md`

- [ ] **Step 1: Write controllers-and-middleware.md**

```markdown
# Controllers & Middleware Guide

Controllers handle HTTP requests. Middleware processes requests before/after controllers.

## Request Lifecycle

\`\`\`
Request → Global Before Middleware → Route Before Middleware → Controller → Route After Middleware → Global After Middleware → Response
\`\`\`

Returning `false` from any middleware short-circuits the pipeline.

See [Request Lifecycle](./request-lifecycle.md) for details.

## Creating Controllers

All controllers extend `Spin\Core\Controller`:

\`\`\`php
<?php
declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class MyController extends Controller
{
    public function handleGET(array \$args): ResponseInterface
    {
        return responseJson(['message' => 'Hello']);
    }
}
\`\`\`

### HTTP Methods

Controllers can implement handlers for different HTTP methods:

- `handleGET(array \$args): ResponseInterface`
- `handlePOST(array \$args): ResponseInterface`
- `handlePUT(array \$args): ResponseInterface`
- `handleDELETE(array \$args): ResponseInterface`
- `handlePATCH(array \$args): ResponseInterface`
- `handleHEAD(array \$args): ResponseInterface`
- `handleOPTIONS(array \$args): ResponseInterface`

Only implement methods you need.

### REST API Controller

\`\`\`php
<?php
declare(strict_types=1);

namespace App\Controllers\Api\V1;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    public function handleGET(array \$args): ResponseInterface
    {
        // GET /api/v1/users/{id}
        \$userId = \$args['id'] ?? null;

        if (!isset(\$userId)) {
            return responseJson(['error' => 'User ID required'], 400);
        }

        \$user = \$this->fetchUser(\$userId);
        return responseJson(\$user ?? ['error' => 'Not found'], \$user ? 200 : 404);
    }

    public function handlePOST(array \$args): ResponseInterface
    {
        // POST /api/v1/users
        \$data = getRequest()->getParsedBody();

        if (empty(\$data['name'] ?? null)) {
            return responseJson(['error' => 'Name required'], 400);
        }

        \$userId = \$this->createUser(\$data);
        return responseJson(['id' => \$userId], 201);
    }

    private function fetchUser(int \$id): ?array
    {
        // Implement database fetch
        return null;
    }

    private function createUser(array \$data): int
    {
        // Implement database insert
        return 0;
    }
}
\`\`\`

### Web Page Controller

\`\`\`php
<?php
declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class BlogController extends Controller
{
    public function handleGET(array \$args): ResponseInterface
    {
        \$postId = \$args['id'] ?? null;

        // Fetch post from database
        \$post = \$this->fetchPost(\$postId);

        if (!post) {
            return response(404);  // Not found
        }

        // Render template
        \$html = view('blog/post', ['post' => \$post]);

        return response(200)
            ->withBody(stream(\$html))
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    private function fetchPost(?int \$id): ?array
    {
        // Implement database fetch
        return null;
    }
}
\`\`\`

## Global Helper Functions

Available in all controllers:

\`\`\`php
// HTTP
getRequest();          // Current PSR-7 ServerRequest
getResponse();         // Current PSR-7 Response
response(\$status);    // Create response with status
responseJson(\$data, \$status);  // JSON response

// Configuration
config();              // Get all config
config('key.nested');  // Get nested config value
env('VAR_NAME');       // Get environment variable

// Application
app();                 // Container instance
logger();              // Monolog instance
cache('adapter.name'); // Cache instance
view(\$template, \$data);  // Render template
\`\`\`

Example:

\`\`\`php
class MyController extends Controller
{
    public function handleGET(array \$args): ResponseInterface
    {
        \$debug = config('app.debug');
        logger()->info('User accessed endpoint');

        return responseJson([
            'debug' => \$debug,
            'app' => app()->version()
        ]);
    }
}
\`\`\`

## Registering Controllers in Routes

Controllers are registered in `config-{env}.json`:

\`\`\`json
{
  "groups": [
    {
      "name": "API v1",
      "prefix": "/api/v1",
      "before": [],
      "routes": [
        {
          "methods": ["GET", "POST"],
          "path": "/users",
          "handler": "\\\\App\\\\Controllers\\\\Api\\\\V1\\\\UserController"
        },
        {
          "methods": ["GET"],
          "path": "/users/{id}",
          "handler": "\\\\App\\\\Controllers\\\\Api\\\\V1\\\\UserController"
        }
      ],
      "after": []
    }
  ]
}
\`\`\`

Path parameters (`{id}`, `{slug}`) are extracted and passed as `\$args`:

\`\`\`php
public function handleGET(array \$args): ResponseInterface
{
    \$id = \$args['id'];  // From {id} in path
    \$slug = \$args['slug'];  // From {slug} in path
}
\`\`\`

## Creating Middleware

All middleware extends `Spin\Core\Middleware`:

\`\`\`php
<?php
declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class MyMiddleware extends Middleware
{
    public function initialize(array \$args): bool
    {
        // Setup: load config, initialize
        // Return false to prevent execution
        return true;
    }

    public function handle(array \$args): bool
    {
        // Per-request logic
        // Return false to short-circuit
        return true;
    }
}
\`\`\`

### Before Middleware Example

Runs before controller:

\`\`\`php
<?php
declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class AuthMiddleware extends Middleware
{
    public function initialize(array \$args): bool
    {
        return true;
    }

    public function handle(array \$args): bool
    {
        \$token = getRequest()->getHeaderLine('Authorization');

        if (!this->validateToken(\$token)) {
            logger()->warning('Invalid token');
            return false;  // Stop pipeline
        }

        return true;  // Continue to controller
    }

    private function validateToken(string \$token): bool
    {
        // Implement token validation
        return !empty(\$token);
    }
}
\`\`\`

### After Middleware Example

Runs after controller:

\`\`\`php
<?php
declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class ResponseLogMiddleware extends Middleware
{
    public function initialize(array \$args): bool
    {
        return true;
    }

    public function handle(array \$args): bool
    {
        \$status = getResponse()->getStatusCode();
        \$method = getRequest()->getMethod();
        \$path = getRequest()->getUri()->getPath();

        logger()->info(sprintf(
            '%s %s → %d',
            \$method,
            \$path,
            \$status
        ));

        return true;
    }
}
\`\`\`

## Registering Middleware

Middleware runs globally or per-route group. Registered in routes config:

\`\`\`json
{
  "common": {
    "before": [],
    "after": ["\\\\App\\\\Middlewares\\\\ResponseLogMiddleware"]
  },
  "groups": [
    {
      "name": "API",
      "prefix": "/api/v1",
      "before": ["\\\\App\\\\Middlewares\\\\AuthMiddleware"],
      "routes": [...],
      "after": []
    }
  ]
}
\`\`\`

Execution order:
1. Global before middleware
2. Group before middleware
3. Controller
4. Group after middleware
5. Global after middleware

If any `handle()` returns `false`, pipeline stops.

## Error Handling

Two special controllers handle errors:

\`\`\`json
{
  "errors": {
    "4xx": "\\\\App\\\\Controllers\\\\Error4xxController",
    "5xx": "\\\\App\\\\Controllers\\\\Error5xxController"
  }
}
\`\`\`

\`\`\`php
<?php
declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class Error4xxController extends Controller
{
    public function handleGET(array \$args): ResponseInterface
    {
        \$status = \$args['status'] ?? 404;

        return response(\$status)
            ->withBody(stream(\$this->renderError(\$status)))
            ->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    private function renderError(int \$status): string
    {
        return view('errors/error-4xx', ['status' => \$status]);
    }
}
\`\`\`

## See Also

- [Request Lifecycle](./request-lifecycle.md)
- [Testing Guide](./testing.md)
- [Getting Started](./getting-started.md)
\`\`\`

- [ ] **Step 2: Commit**

```bash
git add doc/controllers-and-middleware.md
git commit -m "docs: add controllers and middleware guide"
```

---

### Task 6: Write testing.md

**Files:**
- Create: `doc/testing.md`

- [ ] **Step 1: Write testing.md**

```markdown
# Testing Guide

Spin-Skeleton uses PHPUnit for unit and integration tests. Tests mirror the `src/` directory structure.

## Running Tests

### Run All Tests

\`\`\`bash
composer test
\`\`\`

### Run Specific Test File

\`\`\`bash
composer test -- tests/Unit/Controllers/UserControllerTest.php
\`\`\`

### Run Specific Test Method

\`\`\`bash
composer test -- tests/Unit/Controllers/UserControllerTest.php --filter testGetUserReturns200
\`\`\`

### Run with Verbose Output

\`\`\`bash
composer test -- -v
\`\`\`

### Generate Coverage Report

Requires Xdebug or PCOV:

\`\`\`bash
composer test -- --coverage-html coverage/
open coverage/index.html
\`\`\`

## Test Structure

Tests live in `tests/` mirroring `src/`:

\`\`\`
tests/
├── Unit/
│   └── Controllers/
│       ├── Api/
│       │   └── UserControllerTest.php
│       └── IndexControllerTest.php
├── Middlewares/
│   └── AuthMiddlewareTest.php
└── bootstrap.php
\`\`\`

## Writing Controller Tests

\`\`\`php
<?php
declare(strict_types=1);

namespace Tests\Unit\Controllers\Api;

use PHPUnit\Framework\TestCase;
use App\Controllers\Api\V1\UserController;
use Psr\Http\Message\ServerRequestInterface;

class UserControllerTest extends TestCase
{
    private UserController \$controller;

    protected function setUp(): void
    {
        \$this->controller = new UserController();
    }

    public function testHandleGETReturnsUserJson(): void
    {
        // Arrange
        \$request = \$this->createMockRequest('GET', '/api/v1/users/1');
        \$args = ['id' => '1'];

        // Act
        \$response = \$this->controller->handleGET(\$args);

        // Assert
        \$this->assertEquals(200, \$response->getStatusCode());
        \$this->assertStringContainsString('application/json',
            \$response->getHeaderLine('Content-Type'));
    }

    public function testHandleGETWithMissingIdReturns400(): void
    {
        \$args = [];
        \$response = \$this->controller->handleGET(\$args);

        \$this->assertEquals(400, \$response->getStatusCode());
    }

    public function testHandlePOSTCreatesUser(): void
    {
        // Arrange
        \$request = \$this->createMockRequest('POST', '/api/v1/users');
        \$this->setRequestBody(['name' => 'John Doe']);

        // Act
        \$response = \$this->controller->handlePOST([]);

        // Assert
        \$this->assertEquals(201, \$response->getStatusCode());
    }

    private function createMockRequest(string \$method, string \$path): ServerRequestInterface
    {
        // Mock or use test utilities
        return getRequest();  // Use actual request from app
    }

    private function setRequestBody(array \$data): void
    {
        // Mock request body with \$data
    }
}
\`\`\`

## Writing Middleware Tests

\`\`\`php
<?php
declare(strict_types=1);

namespace Tests\Unit\Middlewares;

use PHPUnit\Framework\TestCase;
use App\Middlewares\AuthMiddleware;

class AuthMiddlewareTest extends TestCase
{
    private AuthMiddleware \$middleware;

    protected function setUp(): void
    {
        \$this->middleware = new AuthMiddleware();
    }

    public function testInitializeReturnsTrue(): void
    {
        \$result = \$this->middleware->initialize([]);
        \$this->assertTrue(\$result);
    }

    public function testHandleWithValidTokenReturnsTrue(): void
    {
        // Mock request with valid token
        // \$this->mockRequestHeader('Authorization', 'Bearer valid-token');

        \$result = \$this->middleware->handle([]);
        \$this->assertTrue(\$result);
    }

    public function testHandleWithInvalidTokenReturnsFalse(): void
    {
        // Mock request with invalid token
        // \$this->mockRequestHeader('Authorization', 'Bearer invalid');

        \$result = \$this->middleware->handle([]);
        \$this->assertFalse(\$result);
    }

    public function testHandleWithoutTokenReturnsFalse(): void
    {
        // Mock request with no token

        \$result = \$this->middleware->handle([]);
        \$this->assertFalse(\$result);
    }
}
\`\`\`

## Test Utilities

Create a base test class for common functionality:

\`\`\`php
<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

abstract class IntegrationTestCase extends TestCase
{
    protected function createGetRequest(string \$path): ServerRequestInterface
    {
        return getRequest()
            ->withMethod('GET')
            ->withUri(getRequest()->getUri()->withPath(\$path));
    }

    protected function createPostRequest(string \$path, array \$body): ServerRequestInterface
    {
        return getRequest()
            ->withMethod('POST')
            ->withUri(getRequest()->getUri()->withPath(\$path))
            ->withParsedBody(\$body);
    }

    protected function getJsonResponse(string \$json): array
    {
        return json_decode(\$json, true);
    }
}
\`\`\`

## Database Testing

For tests requiring database, use SQLite in-memory:

Configure `config-unittest.json`:

\`\`\`json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "sqlite",
      "filename": ":memory:"
    }
  }
}
\`\`\`

In test setup:

\`\`\`php
protected function setUp(): void
{
    parent::setUp();

    // Create schema
    \$db = app()->connections()->connection('default');
    \$db->exec(file_get_contents('tests/fixtures/schema.sql'));

    // Seed test data
    \$db->exec(file_get_contents('tests/fixtures/seeds.sql'));
}
\`\`\`

## Best Practices

1. **Name tests clearly:** `testHandleGETWithValidIdReturns200`
2. **Use AAA pattern:** Arrange, Act, Assert
3. **One assertion per test:** Focus on single behavior
4. **Mock external dependencies:** Don't hit real APIs/databases
5. **Keep tests independent:** Don't rely on test execution order
6. **Use fixtures:** Store test data in `tests/fixtures/`

## See Also

- [Getting Started](./getting-started.md)
- [Controllers & Middleware](./controllers-and-middleware.md)
- [Database Connections](./database-connections.md)
\`\`\`

- [ ] **Step 2: Commit**

```bash
git add doc/testing.md
git commit -m "docs: add testing guide"
```

---

### Task 7: Rewrite README.md

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Read current README**

Current README is outdated, focuses on Apache config. Will replace with modern, action-focused version.

- [ ] **Step 2: Write new README.md**

```markdown
# Spin-Skeleton

A lightweight starter application for the [SPIN Framework](https://github.com/Celarius/spin-framework) — a PHP 8+ framework for building web apps and REST APIs.

## Features

✓ REST API example endpoints (`/api/health`, `/api/status`, `/api/info`)
✓ Web page controllers with Plates templating
✓ JSON-based routing and configuration
✓ Session management (APCu, Redis, file-based)
✓ Database connections (MySQL, PostgreSQL, SQLite, Firebird)
✓ Request/response middleware pipeline
✓ Error handling (4xx/5xx controllers)
✓ Logging (Monolog integration)
✓ Caching adapters (APCu, Redis, file-based)
✓ PHPUnit test suite
✓ PSR-7/PSR-11 compliant

## Getting Started (5 Minutes)

### Prerequisites

- PHP 8.0+
- Composer
- Git

### 1. Clone & Install

\`\`\`bash
git clone https://github.com/Celarius/spin-skeleton.git my-app
cd my-app
rm -rf .git
git init
composer install
\`\`\`

### 2. Set Environment

\`\`\`bash
cp .env.example .env
# Edit .env and set APPLICATION_SECRET=your-secret-key
\`\`\`

### 3. Run the App

\`\`\`bash
php -S localhost:8000 -t src/public src/public/bootstrap.php
\`\`\`

Visit `http://localhost:8000` — you should see the home page.

### 4. Verify API Works

\`\`\`bash
curl http://localhost:8000/api/health
# Response: {"status":"ok"}
\`\`\`

### 5. Run Tests

\`\`\`bash
composer test
\`\`\`

All tests should pass ✓

## Documentation

| Guide | Purpose |
|-------|---------|
| [Getting Started](./doc/getting-started.md) | Setup, verify, explore project structure |
| [Configuration](./doc/configuration.md) | Environment vars, config files, secrets |
| [Database Connections](./doc/database-connections.md) | MySQL, SQLite, PostgreSQL, Firebird setup |
| [Controllers & Middleware](./doc/controllers-and-middleware.md) | Create API and web controllers, middleware |
| [Testing](./doc/testing.md) | Write and run tests |

See [SPIN Framework docs](https://github.com/Celarius/spin-framework) for deeper topics (routing, caching, logging, etc.).

## Project Structure

\`\`\`
src/
  app/
    Classes/           # Business logic, managers
    Controllers/       # HTTP request handlers
    Middlewares/       # Request/response pipeline
    Views/             # Templates (Plates)
    Config/
      config-{env}.json    # Environment-specific config
      routes-{env}.json    # Route definitions
  public/
    bootstrap.php      # Application entry point
storage/
  logs/                # Application logs
  cache/               # Cache files
tests/                 # PHPUnit tests
doc/                   # Documentation
\`\`\`

## Common Tasks

### Add a Database

1. Edit `.env` with database credentials
2. Update `config-dev.json` with connection details
3. See [Database Connections](./doc/database-connections.md) for examples

### Create an API Endpoint

1. Create controller in `src/app/Controllers/Api/`
2. Register route in `config-dev.json`
3. See [Controllers & Middleware](./doc/controllers-and-middleware.md) for examples

### Add Middleware

1. Create middleware in `src/app/Middlewares/`
2. Register in `config-dev.json` (global or per-route)
3. See [Controllers & Middleware](./doc/controllers-and-middleware.md) for examples

### Write Tests

1. Create test in `tests/` (mirroring `src/` structure)
2. Run with `composer test`
3. See [Testing](./doc/testing.md) for examples

## Deployment

### Apache

Update `.htaccess` to point to your domain:

\`\`\`bash
SetEnv ENVIRONMENT prod
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ bootstrap.php [QSA,L]
\`\`\`

Or use VirtualHost:

\`\`\`apache
<VirtualHost *:80>
  ServerName example.com
  DocumentRoot /path/to/spin-skeleton/src/public
  SetEnv ENVIRONMENT prod
  SetEnv APPLICATION_SECRET your-secret-key
</VirtualHost>
\`\`\`

### Docker

Build and run:

\`\`\`bash
docker build -t my-app .
docker run -p 8000:8000 -e ENVIRONMENT=prod my-app
\`\`\`

## Links

- [SPIN Framework](https://github.com/Celarius/spin-framework)
- [FastRoute](https://github.com/nikic/FastRoute)
- [Plates](https://platesphp.com/)
- [Monolog](https://github.com/Seldaek/monolog)
- [PSR Standards](https://www.php-fig.org/)

## License

MIT License. See [LICENSE](./LICENSE) for details.

## Author

Kim Sandell (sandell@celarius.com)
\`\`\`

- [ ] **Step 2: Verify new README**

Check that:
- It's modern and action-focused
- Links to all guides are correct
- Quick-start commands work
- Common tasks are linked to docs

- [ ] **Step 3: Commit**

```bash
git add README.md
git commit -m "docs: rewrite README for clarity and action"
```

---

## Chunk 3: Code Standards Alignment

### Task 8: Add `declare(strict_types=1);` to PHP Files

**Files:**
- Modify: All PHP files in `src/app/` missing the declaration

- [ ] **Step 1: Identify files missing declaration**

Based on audit from Task 1, create list of files needing this added.

Expected files:
- Globals.php
- Controllers/*.php
- Middlewares/*.php
- Classes/Managers/*.php

- [ ] **Step 2-N: Add to each file (one commit per file)**

For each file missing `declare(strict_types=1);`:

**File:** `src/app/Globals.php`

Read current content:

```php
<?php

namespace App;
```

Change to:

```php
<?php
declare(strict_types=1);

namespace App;
```

Commit:

```bash
git add src/app/Globals.php
git commit -m "chore: add strict types declaration to Globals.php"
```

**File:** `src/app/Controllers/AbstractPlatesController.php`

Read current content, add declaration after `<?php`, commit.

```bash
git add src/app/Controllers/AbstractPlatesController.php
git commit -m "chore: add strict types declaration to AbstractPlatesController"
```

**File:** `src/app/Controllers/AbstractRestController.php`

Read, add, commit:

```bash
git add src/app/Controllers/AbstractRestController.php
git commit -m "chore: add strict types declaration to AbstractRestController"
```

**Continue for all remaining files:**

- `src/app/Controllers/Api/HealthController.php`
- `src/app/Controllers/Api/InfoController.php`
- `src/app/Controllers/Api/StatusController.php`
- `src/app/Controllers/Api/V1/Sessions/SessionController.php`
- `src/app/Controllers/DefaultController.php`
- `src/app/Controllers/Error4xxController.php`
- `src/app/Controllers/Error5xxController.php`
- `src/app/Controllers/ExampleController.php`
- `src/app/Controllers/IndexController.php`
- `src/app/Middlewares/AuthHttpBeforeMiddleware.php`
- `src/app/Middlewares/ClientInfoBeforeMiddleware.php`
- `src/app/Middlewares/CorsBeforeMiddleware.php`
- `src/app/Middlewares/RequestIdAfterMiddleware.php`
- `src/app/Middlewares/RequestIdBeforeMiddleware.php`
- `src/app/Middlewares/ResponseLogAfterMiddleware.php`
- `src/app/Middlewares/ResponseTimeAfterMiddleware.php`
- `src/app/Middlewares/SessionBeforeMiddleware.php`
- `src/app/Classes/Managers/AbstractManager.php`
- `src/app/Classes/Managers/SessionManager.php`

For each file: read → add declaration → commit with `chore: add strict types declaration to [filename]`

---

### Task 9: Verify Docblock Completeness

**Files:**
- Check: All public methods in `src/app/**/*.php`

- [ ] **Step 1: Spot-check a few files for docblock quality**

Examples to verify:
- `src/app/Controllers/AbstractPlatesController.php` - all public methods have docblocks?
- `src/app/Middlewares/AuthHttpBeforeMiddleware.php` - all public methods documented?
- `src/app/Classes/Managers/SessionManager.php` - return types documented?

- [ ] **Step 2: If issues found, note them for future work**

Create list (not committed):

\`\`\`
DOCBLOCK_ISSUES.txt
===================

Files needing docblock updates:
- [filename] - [issue description]
\`\`\`

For this sprint, document but don't fix (unless critical).

---

## Chunk 4: Compilation & Review

### Task 10: Create IMPROVEMENTS.md

**Files:**
- Create: `IMPROVEMENTS.md`

- [ ] **Step 1: Write IMPROVEMENTS.md**

```markdown
# Spin-Skeleton Improvements (Ideas for Future Work)

This document lists non-breaking enhancements to consider in future iterations. These are suggestions, not required changes.

## Models & ORM Patterns

Add an example Models directory showing:
- Model base class with common queries
- Repository pattern example
- Active Record pattern example

Example structure:

\`\`\`
src/app/Models/
  BaseModel.php
  User.php
  Post.php
  Repository/
    UserRepository.php
\`\`\`

## Database Migrations

Add a simple migration system or example:

\`\`\`bash
php console migrate:create create_users_table
php console migrate:up
\`\`\`

Store migrations in `src/app/Migrations/`.

## API Versioning

Add example of API versioning patterns:

\`\`\`
/api/v1/users
/api/v2/users
\`\`\`

With request routing based on version header or path prefix.

## Authentication Middleware

Expand `AuthHttpBeforeMiddleware` example to show:
- JWT token validation
- Bearer token extraction
- Permission/role checking

## CORS Middleware

Improve `CorsBeforeMiddleware` with:
- Configurable allowed origins
- Preflight request handling
- Custom header support

## Rate Limiting Middleware

Add example rate-limiting middleware:

\`\`\`php
class RateLimitMiddleware extends Middleware {
    // Limit requests per IP per minute
    // Store in Redis or APCu cache
}
\`\`\`

## Environment-Specific Examples

Add config examples for:

\`\`\`
config-prod.json     # Production optimizations
config-staging.json  # Staging environment
\`\`\`

With best-practice settings for each.

## Data Seeding

Add example seed script:

\`\`\`bash
php seed.php          # Run all seeds
php seed.php UserSeeder  # Run specific seeder
\`\`\`

Stores seeder classes in `src/app/Seeds/`.

## Docker Compose

Add `docker-compose.yml` for local development:

\`\`\`yaml
version: '3'
services:
  app:
    build: .
    ports:
      - "8000:8000"
  mysql:
    image: mysql:8
    environment:
      MYSQL_DATABASE: app
      MYSQL_PASSWORD: password
  redis:
    image: redis:7
\`\`\`

## CI/CD Pipeline

Add GitHub Actions workflow:

\`\`\`.github/workflows/test.yml
name: Tests
on: [push]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: php-actions/composer@v6
      - run: composer test
\`\`\`

## Command-Line Tools

Add `Makefile` for common tasks:

\`\`\`makefile
.PHONY: install test serve clean

install:
\tcomposer install

test:
\tcomposer test

serve:
\tphp -S localhost:8000 -t src/public src/public/bootstrap.php

clean:
\trm -rf storage/logs/* storage/cache/*
\`\`\`

## Validation Library

Add form validation examples:

\`\`\`php
\$validator = new Validator(\$_POST);
\$validator->required('name')
          ->email('email')
          ->minLength('password', 8);

if (!validator->validate()) {
    return responseJson(\$validator->errors(), 422);
}
\`\`\`

## Advanced Testing

Add examples of:
- Mocking database connections
- Testing with fixtures
- Integration tests
- End-to-end tests

## Documentation

Future doc improvements:
- API documentation (OpenAPI/Swagger)
- Architecture decision records (ADRs)
- Troubleshooting guide
- Performance tuning guide

---

**Next Steps:**

Pick improvements from above, discuss with team, and create issues for future work.
\`\`\`

- [ ] **Step 2: Review IMPROVEMENTS.md**

This document is for your review. It will NOT be committed. Decide which improvements to pursue in future iterations.

---

### Task 11: Final Verification

- [ ] **Step 1: Verify all documents created**

Check that these files exist and are readable:

\`\`\`bash
ls -la doc/getting-started.md
ls -la doc/configuration.md
ls -la doc/database-connections.md
ls -la doc/controllers-and-middleware.md
ls -la doc/testing.md
ls -la README.md
\`\`\`

- [ ] **Step 2: Run tests to verify nothing broke**

\`\`\`bash
composer test
\`\`\`

Expected: All tests pass ✓

- [ ] **Step 3: Quick manual verification**

Start the app and verify basic flow:

\`\`\`bash
php -S localhost:8000 -t src/public src/public/bootstrap.php
\`\`\`

Visit: `http://localhost:8000`
API call: `curl http://localhost:8000/api/health`

Expected: App runs, API responds ✓

- [ ] **Step 4: Review git log**

\`\`\`bash
git log --oneline -15
\`\`\`

Expected: Multiple commits for docs and standards fixes

- [ ] **Step 5: Final commit summary**

\`\`\`bash
git log --oneline --all | head -20
\`\`\`

---

## Summary

Deliverables completed:

✓ Rewritten README.md (modern, action-focused)
✓ doc/getting-started.md (5-minute setup guide)
✓ doc/configuration.md (env vars, config structure)
✓ doc/database-connections.md (MySQL, SQLite, PostgreSQL examples)
✓ doc/controllers-and-middleware.md (create API/web controllers, middleware)
✓ doc/testing.md (write and run tests)
✓ declare(strict_types=1) added to all PHP files
✓ Code standards aligned with SPIN Framework
✓ IMPROVEMENTS.md created for future work
✓ All tests passing

---

## Success Criteria

✓ New developer can clone, install, run, verify in <5 minutes
✓ Test suite passes on fresh clone
✓ All PHP files declare strict types
✓ Documentation covers: getting started, config, database, controllers, testing
✓ IMPROVEMENTS.md provides roadmap for future enhancements
✓ Code follows SPIN Framework standards

```

---

Plan complete and saved to `docs/superpowers/plans/2026-03-16-documentation-and-standards-implementation.md`. Ready to execute?