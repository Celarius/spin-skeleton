---
name: spin-helpers
description: Use when using or extending Spin PHP's global helper functions, the PSR-11 dependency container, PSR-3 logger, or Globals.php custom helpers. Covers all framework-provided helpers (app, config, env, container, logger, db, cache, getRequest, getResponse, response*, redirect, queryParam, postParam, cookieParam, generateRefId, getClientIp), the Globals.php extension pattern, and logging best practices. Always load when writing code that calls any of these helpers, adding new app-wide utility functions to Globals.php, or debugging container/logger issues.
---

# Spin Helpers — Reference

Global helpers are available everywhere in Spin — controllers, middleware, services. No imports required.

---

## Complete Helper Reference

### Application & Infrastructure

| Helper | Signature | Returns | Description |
|--------|-----------|---------|-------------|
| `app()` | `app(): Application` | `\Spin\Application` | Global application instance |
| `config()` | `config(?string $key, mixed $val = null): mixed` | mixed | Read/write config (dot-notation). `config('a.b.c')` reads; `config('a.b', $v)` writes. |
| `env()` | `env(string $var, mixed $default = null): mixed` | mixed | Read OS env / `.env` variable with optional default |
| `container()` | `container(?string $id = null, mixed $val = null): mixed` | mixed | Get/set PSR-11 DI container |
| `logger()` | `logger(): Logger` | Monolog Logger | PSR-3 logger instance |

### Database & Cache

| Helper | Signature | Returns | Description |
|--------|-----------|---------|-------------|
| `db()` | `db(string $name): PdoConnection\|null` | PDO or null | Named PDO connection (see `spin-database`) |
| `cache()` | `cache(string $name): CacheInterface\|null` | PSR-16 or null | Named cache adapter (see `spin-cache`) |

### Request

| Helper | Signature | Returns | Description |
|--------|-----------|---------|-------------|
| `getRequest()` | `getRequest(): ServerRequestInterface` | PSR-7 Request | Current request object |
| `queryParam()` | `queryParam(string $name, mixed $default = null): mixed` | mixed | Single URL query param (`?foo=bar`) |
| `queryParams()` | `queryParams(): array` | array | All URL query params as associative array |
| `postParam()` | `postParam(string $name, mixed $default = null): mixed` | mixed | Single form POST param |
| `postParams()` | `postParams(): array` | array | All form POST params |
| `cookieParam()` | `cookieParam(string $name): mixed` | mixed or null | Single cookie value |
| `getClientIp()` | `getClientIp(): string` | string | Client IP address |

### Response

| Helper | Signature | Description |
|--------|-----------|-------------|
| `getResponse()` | `getResponse(): ResponseInterface` | Current response object |
| `response()` | `response(string $body = '', int $code = 200, array $headers = []): Response` | Plain HTTP response |
| `responseJson()` | `responseJson(array $data = [], int $code = 200, int $options = 0, array $headers = []): Response` | JSON response (sets Content-Type) |
| `responseHtml()` | `responseHtml(string $body = '', int $code = 200, array $headers = []): Response` | HTML response |
| `responseXml()` | `responseXml(array $data = [], string $root = 'xml', int $code = 200, array $headers = []): Response` | XML response |
| `responseFile()` | `responseFile(string $filename, int $code = 200, array $headers = [], bool $remove = false): Response` | File download response |
| `redirect()` | `redirect(string $uri, int $status = 302, array $headers = []): Response` | HTTP redirect |
| `responseJsonError()` | See below | Structured error response (**Globals.php**, not framework) |

### Utilities

| Helper | Signature | Description |
|--------|-----------|-------------|
| `generateRefId()` | `generateRefId(string $prefix = ''): string` | Generate unique reference ID |
| `getClientIp()` | `getClientIp(): string` | Client remote IP (handles proxies) |

---

## container() — PSR-11 DI

The container stores request-scoped values, services, and lazy factories:

```php
// Store a value
container('requestId', 'req_abc123');
container('jwt_claims', $payload);
container('association_id', 42);

// Retrieve (returns null if key not found)
$requestId = container('requestId');
$claims    = container('jwt_claims');

// Store a callable factory — resolved lazily on first access
container('mailer', function() {
    return new \App\Classes\MailerService(config('mail'));
});
$mailer = container('mailer');  // factory called here, once
```

### Common Container Keys

| Key | Set by | Contains |
|-----|--------|---------|
| `requestId` | `RequestIdBeforeMiddleware` | Unique request ID string (`req_...`) |
| `jwt_claims` | `AuthJwtBeforeMiddleware` | JWT payload array (`sub`, `exp`, `nbf`, ...) |
| `jwt_token` | `AuthJwtBeforeMiddleware` | Raw JWT token string |

---

## logger() — PSR-3 Logging

```php
// All six PSR-3 levels (lowest to highest):
logger()->debug('Cache miss', ['key' => $key]);
logger()->info('User logged in', ['userId' => $id]);
logger()->notice('Config reloaded', ['env' => $env]);
logger()->warning('Slow query', ['ms' => $elapsed, 'sql' => $sql]);
logger()->error('DB connection failed', ['error' => $e->getMessage()]);
logger()->critical('Unhandled exception', [...]);
```

**Standard exception log format** (verified from `TemplateController.php`):

```php
logger()->critical('Exception', [
    'message'   => 'Human-readable description of what was happening',
    'exception' => $e->getMessage(),
    'requestId' => container('requestId'),
    'trace'     => $e->getTraceAsString()
]);
```

**Rules:**
- Always include `requestId` in context — enables trace correlation across log lines
- `message` field describes the operation that failed; `exception` is the raw exception message
- Use appropriate level: `critical` for unexpected exceptions, `warning` for expected validation failures, `info` for significant business events
- Log level configured per environment in `config-{env}.json` under `logger.level`

---

## responseJsonError() — Globals.php

This helper is defined in `App\Globals.php` (not the framework). It produces a standardized error envelope:

```php
responseJsonError(string $title, string $message, int $httpCode = 400, mixed $code = null): Response
```

Produces:
```json
{
  "result":  "ERROR",
  "code":    null,
  "title":   "Bad request",
  "message": "The 'name' field is required",
  "rid":     "req_abc123"
}
```

```php
// Usage examples:
return responseJsonError('Bad request',     "'name' is required",     400);
return responseJsonError('Not Found',       'Item not found',         404);
return responseJsonError('Forbidden',       'Insufficient access',    403);
return responseJsonError('Server Error',    'Failed to process',      500);
return responseJsonError('Conflict',        'Email already in use',   409, 'EMAIL_TAKEN');
```

The `$code` parameter (4th arg) is for app-specific error codes in the `code` field. Leave `null` for generic errors.

Use `responseJsonError` for all error responses — not `responseJson` with an error structure. This ensures consistent envelope format and automatic `rid` field.

---

## Globals.php Extension Pattern

`src/app/Globals.php` is loaded after framework helpers in `bootstrap.php`. Add app-specific global functions here:

```php
<?php declare(strict_types=1);

// Always wrap in function_exists guard (Globals.php may be loaded multiple times in tests)

if (!function_exists('responseJsonError')) {
    function responseJsonError(string $title, string $message, int $httpCode = 400, mixed $code = null)
    {
        return responseJson([
            'result'  => 'ERROR',
            'code'    => $code,
            'title'   => $title,
            'message' => $message,
            'rid'     => container('requestId'),
        ], $httpCode);
    }
}

if (!function_exists('currentAssociationId')) {
    function currentAssociationId(): ?int
    {
        return container('association_id');
    }
}
```

**Rules:**
- Always use `if (!function_exists('...'))` guard
- Keep functions pure or side-effect-free when possible
- Don't put complex business logic in global helpers — use services
- Functions defined here are available everywhere, same as framework helpers

---

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| Wrapping response helpers: `$this->respond(...)` | Call `responseJson()` / `responseJsonError()` directly |
| Using `responseJson` for errors | Use `responseJsonError` — it adds the `rid` field and standardizes envelope |
| `container('key')` returning null unexpectedly | Check if the middleware that sets the key is registered in routes.json |
| Logger without context array | Always pass a context array: `logger()->info('msg', ['key' => $val])` |
| Not including `requestId` in logger context | Always include `'requestId' => container('requestId')` in error logs |
| Missing `if (!function_exists(...))` in Globals.php | Required for test isolation — Globals.php can be included multiple times |
| Calling `generateRefId()` without prefix | For request IDs, use the naming pattern: `'req_' . substr(bin2hex(random_bytes(8)), 0, 16)` |
