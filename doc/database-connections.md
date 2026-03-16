# Database Connections Guide

Spin Framework supports multiple relational databases through PDO. This guide covers setup and configuration for each database system.

## Supported Databases

- **MySQL** / **MariaDB** — `mysql` driver
- **PostgreSQL** — `pgsql` driver
- **SQLite** — `sqlite` driver (file or in-memory)
- **Firebird** — `firebird` driver
- **CockroachDB** — `pgsql` driver (PostgreSQL-compatible)
- **ODBC** — `odbc` driver

## Connection Configuration

Connections are defined in `config-{env}.json` under the `"connections"` section:

```json
{
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
}
```

## MySQL / MariaDB Setup

### 1. Create Database and User

```sql
-- Connect to MySQL as admin
mysql -u root -p

-- Create database
CREATE DATABASE myapp_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with full permissions
CREATE USER 'myapp'@'localhost' IDENTIFIED BY 'secure_password_123';
GRANT ALL PRIVILEGES ON myapp_db.* TO 'myapp'@'localhost';
FLUSH PRIVILEGES;

-- Verify
SHOW GRANTS FOR 'myapp'@'localhost';
```

### 2. Configure Connection

Add to `config-dev.json`:

```json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "myapp_db",
      "host": "localhost",
      "port": "3306",
      "username": "myapp",
      "password": "secure_password_123",
      "charset": "UTF8MB4",
      "options": {
        "ATTR_PERSISTENT": false,
        "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT": false,
        "ATTR_EMULATE_PREPARES": false
      }
    }
  }
}
```

Or with environment variables (recommended):

```json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "${env:DB_DATABASE}",
      "host": "${env:DB_HOST}",
      "port": "${env:DB_PORT}",
      "username": "${env:DB_USERNAME}",
      "password": "${env:DB_PASSWORD}",
      "charset": "UTF8MB4",
      "options": {
        "ATTR_PERSISTENT": false,
        "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT": false
      }
    }
  }
}
```

And `.env`:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=myapp_db
DB_USERNAME=myapp
DB_PASSWORD=secure_password_123
```

### 3. Usage Example

```php
<?php declare(strict_types=1);

namespace App\Controllers\Api;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class UserController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        try {
            // Get connection
            $db = app()->getConnection('default');

            // Execute query
            $stmt = $db->prepare('SELECT id, name, email FROM users WHERE id = ?');
            $stmt->execute([$args['id'] ?? 1]);
            $user = $stmt->fetchAssoc();

            return responseJson(['user' => $user], 200);
        } catch (\Exception $e) {
            logger()->error('User fetch failed', ['error' => $e->getMessage()]);
            return responseJson(['error' => 'Not found'], 404);
        }
    }
}
```

## PostgreSQL Setup

### 1. Create Database and User

```bash
# Connect to PostgreSQL
psql -U postgres

-- Create database
CREATE DATABASE myapp_db
  WITH ENCODING 'UTF8'
  LC_COLLATE 'C'
  LC_CTYPE 'C'
  TEMPLATE template0;

-- Create user
CREATE USER myapp WITH PASSWORD 'secure_password_123';

-- Grant permissions
GRANT ALL PRIVILEGES ON DATABASE myapp_db TO myapp;
GRANT ALL PRIVILEGES ON SCHEMA public TO myapp;

-- Verify
\du
\l
```

### 2. Configure Connection

```json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "pgsql",
      "schema": "${env:DB_DATABASE}",
      "host": "${env:DB_HOST}",
      "port": "${env:DB_PORT}",
      "username": "${env:DB_USERNAME}",
      "password": "${env:DB_PASSWORD}",
      "charset": "UTF8",
      "options": {
        "ATTR_PERSISTENT": false,
        "ATTR_ERRMODE": "ERRMODE_EXCEPTION",
        "ATTR_AUTOCOMMIT": false
      }
    }
  }
}
```

And `.env`:

```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=myapp_db
DB_USERNAME=myapp
DB_PASSWORD=secure_password_123
```

### 3. Usage Example

```php
$db = app()->getConnection('default');

// Create table
$db->exec('
  CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )
');

// Insert data
$stmt = $db->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
$stmt->execute(['John Doe', 'john@example.com']);

// Query
$stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute(['john@example.com']);
$user = $stmt->fetchAssoc();
```

## SQLite for Development and Testing

SQLite requires no server setup and is ideal for local development and testing.

### 1. File-Based Database

Configure in `config-dev.json`:

```json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "sqlite",
      "filename": "storage/database/db.sqlite"
    }
  }
}
```

Create the storage directory:

```bash
mkdir -p storage/database
```

### 2. In-Memory Database (Testing)

For tests, use in-memory SQLite:

```json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "sqlite",
      "filename": ":memory:"
    }
  }
}
```

### 3. Usage Example

```php
$db = app()->getConnection('default');

// Create table
$db->exec('
  CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )
');

// Insert
$stmt = $db->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
$stmt->execute(['Alice Smith', 'alice@example.com']);
$lastId = $db->lastInsertId();

// Query
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$lastId]);
$user = $stmt->fetchAssoc();

// Update
$stmt = $db->prepare('UPDATE users SET name = ? WHERE id = ?');
$stmt->execute(['Alice Johnson', $lastId]);

// Delete
$stmt = $db->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([$lastId]);
```

## Multiple Connections

Define multiple database connections for different purposes:

```json
{
  "connections": {
    "primary": {
      "type": "Pdo",
      "driver": "mysql",
      "schema": "${env:DB_PRIMARY}",
      "host": "${env:DB_PRIMARY_HOST}",
      "username": "${env:DB_PRIMARY_USER}",
      "password": "${env:DB_PRIMARY_PASS}"
    },
    "analytics": {
      "type": "Pdo",
      "driver": "pgsql",
      "schema": "${env:ANALYTICS_DB}",
      "host": "${env:ANALYTICS_HOST}",
      "username": "${env:ANALYTICS_USER}",
      "password": "${env:ANALYTICS_PASS}"
    },
    "cache": {
      "type": "Pdo",
      "driver": "sqlite",
      "filename": "storage/cache.db"
    }
  }
}
```

Access each connection by name:

```php
$primary = app()->getConnection('primary');
$analytics = app()->getConnection('analytics');
$cache = app()->getConnection('cache');
```

## PDO Options Reference

Common PDO options to configure behavior:

| Option | Values | Description |
|--------|--------|-------------|
| `ATTR_PERSISTENT` | `true`, `false` | Persistent connections across requests |
| `ATTR_ERRMODE` | `ERRMODE_SILENT`, `ERRMODE_WARNING`, `ERRMODE_EXCEPTION` | Error reporting mode |
| `ATTR_AUTOCOMMIT` | `true`, `false` | Auto-commit transactions |
| `ATTR_EMULATE_PREPARES` | `true`, `false` | Emulate prepared statements |
| `ATTR_DEFAULT_FETCH_MODE` | `FETCH_ASSOC`, `FETCH_OBJ`, `FETCH_ARRAY` | Default fetch mode |
| `ATTR_TIMEOUT` | integer (seconds) | Connection timeout |

Example configuration with common options:

```json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "mysql",
      "host": "${env:DB_HOST}",
      "schema": "${env:DB_DATABASE}",
      "username": "${env:DB_USERNAME}",
      "password": "${env:DB_PASSWORD}",
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
```

## Query Execution Examples

### SELECT Query

```php
$db = app()->getConnection('default');

// Single row
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([1]);
$user = $stmt->fetchAssoc();
// Result: ['id' => 1, 'name' => 'John', ...]

// Multiple rows
$stmt = $db->prepare('SELECT * FROM users WHERE status = ?');
$stmt->execute(['active']);
$users = $stmt->fetchAll();
// Result: [['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']]

// Count
$stmt = $db->prepare('SELECT COUNT(*) as count FROM users');
$stmt->execute();
$result = $stmt->fetchAssoc();
echo $result['count']; // 42
```

### INSERT Query

```php
$stmt = $db->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
$stmt->execute(['Bob Brown', 'bob@example.com']);
$insertId = $db->lastInsertId();
```

### UPDATE Query

```php
$stmt = $db->prepare('UPDATE users SET name = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute(['Bob Johnson', 1]);
$rowsAffected = $stmt->rowCount();
echo "Updated $rowsAffected rows";
```

### DELETE Query

```php
$stmt = $db->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([1]);
echo "Deleted " . $stmt->rowCount() . " rows";
```

### Transactions

```php
try {
    $db->beginTransaction();

    // Multiple operations
    $stmt1 = $db->prepare('INSERT INTO accounts (name) VALUES (?)');
    $stmt1->execute(['New Account']);
    $accountId = $db->lastInsertId();

    $stmt2 = $db->prepare('INSERT INTO balance (account_id, amount) VALUES (?, ?)');
    $stmt2->execute([$accountId, 0]);

    $db->commit();
    return responseJson(['success' => true, 'account_id' => $accountId]);

} catch (\Exception $e) {
    $db->rollBack();
    logger()->error('Transaction failed', ['error' => $e->getMessage()]);
    return responseJson(['error' => 'Operation failed'], 500);
}
```

## Testing with SQLite In-Memory

Use in-memory SQLite for fast, isolated tests:

In `config-unittest.json`:

```json
{
  "connections": {
    "default": {
      "type": "Pdo",
      "driver": "sqlite",
      "filename": ":memory:"
    }
  }
}
```

In your test class:

```php
<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        // Create test tables
        $db = app()->getConnection('default');
        $db->exec('
          CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL
          )
        ');
    }

    public function testInsertUser(): void
    {
        $db = app()->getConnection('default');

        $stmt = $db->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
        $stmt->execute(['Test User', 'test@example.com']);

        $this->assertEquals(1, $db->lastInsertId());
    }
}
```

## See Also

- [Getting Started Guide](getting-started.md)
- [Configuration Guide](configuration.md)
- [Testing Guide](testing.md)
- [Controllers & Middleware Guide](controllers-and-middleware.md)
