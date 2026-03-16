# Getting Started with Spin Skeleton (5 minutes)

This guide will have you up and running with the Spin Framework skeleton in minutes.

## Prerequisites

Before you start, ensure you have installed:

- **PHP 8.0+** - Check with `php --version`
- **Composer** - Check with `composer --version` (install from [getcomposer.org](https://getcomposer.org))
- **Git** - Check with `git --version`

## Step 1: Clone the Repository

```bash
git clone https://github.com/Celarius/spin-skeleton.git myapp
cd myapp
```

Or, if starting fresh:

```bash
git clone https://github.com/Celarius/spin-skeleton.git myapp
cd myapp
rm -rf .git
git init
```

## Step 2: Install Dependencies

```bash
composer install
```

Expected output:
```
Installing dependencies from lock file
Verifying lock file integrity...
Found... in composer.lock, root version: ...
Generating optimized autoload files
```

## Step 3: Configure Environment

Create a `.env` file in the project root (or copy from `.env.example` if provided):

```env
ENVIRONMENT=dev
APPLICATION_SECRET=your-secret-key-here
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=skeleton_db
DB_USERNAME=root
DB_PASSWORD=
REDIS_HOST=localhost
```

Configuration values in `.env` are loaded by the framework and can be referenced in `config-{ENVIRONMENT}.json` files using `${env:VAR_NAME}` syntax.

## Step 4: Run the Development Server

```bash
php -S localhost:8000 -t src/public
```

Expected output:
```
Development Server (http://localhost:8000)
Listening on http://localhost:8000
Press Ctrl-C to quit:
```

## Step 5: Verify the API

Test the health endpoint to ensure everything is working:

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

## Step 6: Run Tests

```bash
composer test
```

Or with verbose output:

```bash
composer test -- --verbose
```

Expected output:
```
PHPUnit 12.0.0 by Sebastian Bergmann and contributors.

...... (dots for passed tests)

Time: 0.123 seconds, Memory: 10.00 MB

OK (6 tests, 6 assertions)
```

## Project Structure

```
spin-skeleton/
├── src/
│   ├── app/
│   │   ├── Config/              # Configuration files
│   │   │   ├── config-dev.json
│   │   │   ├── config-unittest.json
│   │   │   └── routes-dev.json
│   │   ├── Controllers/         # API and page controllers
│   │   │   ├── Api/
│   │   │   │   ├── HealthController.php
│   │   │   │   └── StatusController.php
│   │   │   ├── IndexController.php
│   │   │   └── ExampleController.php
│   │   ├── Middlewares/         # Request/response middleware
│   │   ├── Views/               # Templates
│   │   │   ├── Pages/
│   │   │   ├── Errors/
│   │   │   └── Components/
│   │   ├── Models/              # (future) Data models
│   │   ├── Globals.php          # Global helper registration
│   │   └── Classes/             # Application classes
│   └── public/
│       ├── bootstrap.php        # Application entry point
│       ├── index.php            # Fallback
│       └── .htaccess            # Apache routing rules
├── tests/                       # PHPUnit tests
│   ├── ApplicationTest.php
│   ├── MiddlewareTest.php
│   └── bootstrap.php
├── doc/                         # Documentation
│   ├── getting-started.md
│   ├── configuration.md
│   ├── database-connections.md
│   ├── controllers-and-middleware.md
│   └── testing.md
├── vendor/                      # Composer dependencies
├── .env                         # Environment variables (create)
├── .gitignore
├── composer.json
└── README.md
```

## Next Steps

- **[Configuration Guide](configuration.md)** — Learn how to configure your app
- **[Controllers & Middleware Guide](controllers-and-middleware.md)** — Build your first controller
- **[Database Setup](database-connections.md)** — Connect to MySQL, SQLite, or PostgreSQL
- **[Testing Guide](testing.md)** — Write and run tests

## Troubleshooting

### "Could not find package celarius/spin-framework"

Make sure you're running `composer install` from the project root and have a valid internet connection.

### "Class not found" errors

Run `composer dumpautoload` to regenerate the autoloader:

```bash
composer dumpautoload -o
```

### Port 8000 already in use

Use a different port:

```bash
php -S localhost:8080 -t src/public
```

### .env file not being loaded

1. Verify the `.env` file exists in the project root
2. Check that `ENVIRONMENT` is set to match your config file (e.g., `config-dev.json` requires `ENVIRONMENT=dev`)
3. Verify variable names match exactly (case-sensitive)

### Tests failing

Ensure you have the test database configured. For SQLite in-memory:

```bash
# No configuration needed — SQLite in-memory works out of the box
composer test
```

## Optional: Docker Setup

To run Spin Skeleton in Docker, create a `Dockerfile`:

```dockerfile
FROM php:8.2-cli

# Install extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && docker-php-ext-install mbstring \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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

## Need Help?

- Check the [Configuration Guide](configuration.md) for detailed configuration options
- Visit [spin.celarius.com](http://spin.celarius.com) for more resources
- Review example controllers in `src/app/Controllers/`
