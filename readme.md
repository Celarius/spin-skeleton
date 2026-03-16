# Spin Skeleton

A lightweight, modern PHP 8+ web framework skeleton for building REST APIs and web applications.

## Features

✓ **Lightweight & Fast** — Minimal overhead, optimized for performance
✓ **PHP 8+** — Modern PHP with strict typing and attributes
✓ **REST APIs** — JSON routing and controllers with built-in helpers
✓ **PSR Compliant** — PSR-3, PSR-7, PSR-11, PSR-16, PSR-17 standards
✓ **Flexible Configuration** — JSON config with environment variable expansion
✓ **Database Support** — MySQL, PostgreSQL, SQLite, Firebird, CockroachDB
✓ **Middleware Pipeline** — Global, group, and route-level middleware
✓ **Caching** — APCu, Redis, and file-based cache adapters
✓ **Logging** — Monolog integration with configurable drivers
✓ **Tested** — PHPUnit test suite included

## Getting Started (5 Minutes)

### 1. Clone the Repository

```bash
git clone https://github.com/Celarius/spin-skeleton.git myapp
cd myapp
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Create .env File

```bash
cp .env.example .env
# Edit .env with your settings
```

### 4. Run Development Server

```bash
php -S localhost:8000 -t src/public
```

### 5. Test the API

```bash
curl http://localhost:8000/api/health
```

Expected response:
```json
{
  "status": "OK",
  "code": "spin",
  "name": "Spin Skeleton",
  "version": "0.0.1"
}
```

✓ **Done!** Your app is running. Read the documentation to build your first controller.

## Documentation

| Topic | File |
|-------|------|
| 5-minute setup guide | [Getting Started](doc/getting-started.md) |
| Configuration and environment variables | [Configuration](doc/configuration.md) |
| Database setup and queries (MySQL, SQLite, PostgreSQL) | [Database Connections](doc/database-connections.md) |
| Creating controllers and middleware | [Controllers & Middleware](doc/controllers-and-middleware.md) |
| Writing tests with PHPUnit | [Testing](doc/testing.md) |

## Project Structure

```
spin-skeleton/
├── src/
│   ├── app/
│   │   ├── Config/              # Configuration files (JSON)
│   │   │   ├── config-dev.json
│   │   │   ├── config-unittest.json
│   │   │   └── routes-dev.json
│   │   ├── Controllers/         # API and web controllers
│   │   │   ├── Api/
│   │   │   │   ├── HealthController.php
│   │   │   │   ├── StatusController.php
│   │   │   │   └── InfoController.php
│   │   │   ├── DefaultController.php
│   │   │   └── ExampleController.php
│   │   ├── Middlewares/         # Request/response middleware
│   │   ├── Views/               # Templates and pages
│   │   │   ├── Pages/
│   │   │   ├── Errors/
│   │   │   └── Components/
│   │   ├── Models/              # (Future) Data models
│   │   ├── Classes/             # Application classes
│   │   └── Globals.php          # Global helpers registration
│   └── public/
│       ├── bootstrap.php        # Application entry point
│       ├── index.php
│       └── .htaccess            # Apache routing
├── tests/                       # PHPUnit test suite
│   ├── Controllers/
│   ├── Middlewares/
│   ├── ApplicationTest.php
│   ├── bootstrap.php
│   └── phpunit.xml
├── doc/                         # Documentation guides
│   ├── getting-started.md
│   ├── configuration.md
│   ├── database-connections.md
│   ├── controllers-and-middleware.md
│   └── testing.md
├── vendor/                      # Composer dependencies
├── .env                         # Environment variables (create)
├── .gitignore
├── composer.json
├── composer.lock
└── phpunit.xml
```

## Common Tasks

### Create a REST API Endpoint

1. Create a controller in `src/app/Controllers/Api/`
2. Extend `Spin\Core\Controller`
3. Implement `handleGET()`, `handlePOST()`, etc.
4. Register in `src/app/Config/routes-dev.json`

[Full guide →](doc/controllers-and-middleware.md#rest-api-controller-example)

### Connect to Database

1. Configure connection in `config-dev.json`
2. Set `.env` variables for credentials
3. Use `app()->getConnection()` in controllers

Supports: MySQL, PostgreSQL, SQLite, Firebird, CockroachDB

[Full guide →](doc/database-connections.md)

### Write Tests

1. Create test file in `tests/`
2. Extend `PHPUnit\Framework\TestCase`
3. Use `composer test` to run

[Full guide →](doc/testing.md)

### Configure Application

1. Edit `src/app/Config/config-dev.json`
2. Use environment variables with `${env:VAR}`
3. Access with `config('key.path')` helper

[Full guide →](doc/configuration.md)

### Add Middleware

1. Create class extending `Spin\Core\Middleware`
2. Implement `initialize()` and `handle()` methods
3. Register in config or routes

[Full guide →](doc/controllers-and-middleware.md#creating-middleware)

## Deployment

### Apache VirtualHost

```apache
<VirtualHost *:80>
  ServerName myapp.local
  DocumentRoot /var/www/myapp/src/public

  <Directory /var/www/myapp/src/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ bootstrap.php [QSA,L]
  </Directory>
</VirtualHost>
```

### Docker

```dockerfile
FROM php:8.2-cli
RUN apt-get update && apt-get install -y git unzip
RUN curl https://getcomposer.org/composer.phar -o /usr/local/bin/composer && chmod +x /usr/local/bin/composer
WORKDIR /app
COPY . .
RUN composer install --optimize-autoloader --no-dev
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "src/public"]
```

Build and run:
```bash
docker build -t spin-skeleton .
docker run -p 8000:8000 spin-skeleton
```

## Dependencies

- **PHP 8.0+**
- **celarius/spin-framework** — Core framework
- **league/plates** — Template engine
- **phpunit/phpunit** — Testing framework (dev)

See `composer.json` for all dependencies.

## Testing

Run the test suite:

```bash
composer test
```

With coverage report:

```bash
./vendor/bin/phpunit --coverage-html coverage/
```

## License

MIT License — see [LICENSE](LICENSE) for details

## Author

Kim Sandell ([sandell@celarius.com](mailto:sandell@celarius.com))

---

**Resources:**

- [Spin Framework](https://github.com/Celarius/spin-framework)
- [Documentation](doc/)
- [Getting Started in 5 Minutes](doc/getting-started.md)
