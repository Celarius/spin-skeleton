# CLAUDE.md — Spin Skeleton Application

Guidance for Claude Code when working in this repository.

## Project Overview

**Spin Skeleton** is a starter application built on the [Spin Framework](https://github.com/celarius/spin-framework).
- **Package:** `celarius/spin-skeleton`
- **App namespace:** `App\` → `src/app/`
- **Entry point:** `src/public/bootstrap.php`
- **Author:** Kim Sandell (sandell@celarius.com)
- **License:** MIT

## Repository Layout

```
src/
  app/
    Classes/           # App utility classes (managers, etc.)
    Config/            # config-{env}.json, routes-{env}.json, version.json
    Controllers/       # AbstractController + concrete controllers
    Middlewares/       # AbstractMiddleware + concrete middleware
    Views/             # HTML templates (Plates engine)
    Globals.php        # Custom global helper registrations
  public/
    bootstrap.php      # Application entry point
  storage/
    logs/              # Runtime logs
    cache/             # File cache
tests/                 # PHPUnit test suite (mirrors src/app/)
doc/                   # Markdown documentation
```

## Skills

This project has local skills for all major framework topics. Claude Code will load them automatically when relevant:

| Skill | When loaded |
|-------|------------|
| `spin-framework` | Overview / routing to other skills |
| `spin-controllers` | Writing or modifying controllers |
| `spin-middleware` | Writing or modifying middleware |
| `spin-routing` | Editing `routes-{env}.json` |
| `spin-config` | Editing `config-{env}.json` or `bootstrap.php` |
| `spin-database` | Database queries, repositories, PDO |
| `spin-cache` | Caching with APCu or Redis |
| `spin-helpers` | Global helpers, `Globals.php`, DI container |

## Commands

```bash
# Install dependencies
composer install

# Run tests (Windows)
.\phpunit.cmd

# Run tests (Linux/macOS)
./vendor/bin/phpunit

# Run with coverage (requires Xdebug or PCOV)
./vendor/bin/phpunit --coverage-html coverage/
```

## Application Patterns

### Controllers

All app controllers extend `App\Controllers\AbstractController` (which extends `Spin\Core\Controller`). Only override the HTTP methods you need — unimplemented methods return 405.

```php
declare(strict_types=1);
namespace App\Controllers\Api\V1;

use Psr\Http\Message\ResponseInterface;
use App\Controllers\AbstractController;

class UserController extends AbstractController
{
    public function handleGET(array $args): ResponseInterface
    {
        return responseJson(['id' => $args['id']]);
    }
}
```

### Middleware

All app middleware extends `App\Middlewares\AbstractMiddleware` (which extends `Spin\Core\Middleware`).

### Globals.php

Custom application helpers are registered in `src/app/Globals.php`. Always guard additions with `function_exists()`.

### Configuration

Environment-specific configs live in `src/app/Config/config-{env}.json`. Use `${env:VAR}` macros for secrets. Never hardcode credentials.

### Routes

Routes live in `src/app/Config/routes-{env}.json`. Never define routes in PHP.

## Coding Conventions

- `declare(strict_types=1);` at the top of every PHP file.
- Namespace `App\` maps to `src/app/` via PSR-4.
- Explicit type hints; avoid `mixed`.
- Docblocks on all public methods.
- Tests mirror `src/app/` structure under `tests/`.

## What NOT to Change Without Discussion

- **Global helper signatures** — consumed by all controllers and middleware.
- **`src/app/Config/` JSON schemas** — breaking schema changes break runtime.
- **`App\Controllers\AbstractController`** — base class for all controllers; changes affect everything.
- **`src/public/bootstrap.php`** — application entry point; changes affect startup.

## Commit Messages

Never include "Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>" in commit messages.

## Output Style

Professional, short, and concise. No pleasantries.

## Related Repositories

- **Framework source:** `celarius/spin-framework` (at `vendor/celarius/spin-framework/`)
- **Framework skills:** See `.claude/skills/` in this repo
