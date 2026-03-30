---
name: spin-middleware
description: Use when creating, modifying, or debugging Spin PHP middleware. Covers the Middleware base class contract (initialize/handle returning bool), before vs. after middleware, pipeline control (true=continue, false=stop + setResponse), JWT auth middleware, rate limiting, and how to register middleware in routes.json. Always load when writing middleware logic, debugging pipeline abort behavior, or a before middleware returns unexpected 401/429/500.
---

# Spin Middleware — Reference

---

## Middleware Contract

All middleware extends `Spin\Core\Middleware`. Two methods to implement:

```php
<?php declare(strict_types=1);
namespace App\Middlewares;

use Spin\Core\Middleware;

class MyBeforeMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        // Runs before handle(). Must return bool.
        return true;
    }

    public function handle(array $args): bool
    {
        // Main logic. true = continue pipeline. false = stop pipeline.
        return true;
    }
}
```

**Critical contract rules:**
- `initialize()` MUST return `bool` — unlike Controller's `initialize()` which has no strict return requirement
- `handle()` MUST return `bool`
- Returning `false` stops the entire pipeline — no further middleware or controller will run

---

## Pipeline Semantics

```
common.before → group.before → controller → group.after → common.after
```

Each middleware in the chain returns `true` or `false`:

- `return true` → next middleware/controller runs
- `return false` → pipeline stops immediately

**Before returning `false`, you MUST set a response:**

```php
public function handle(array $args): bool
{
    if (!$this->isAuthorized()) {
        app()->setResponse(
            responseJsonError('Unauthorized', 'Invalid token', 401)
        );
        return false;  // pipeline stops; the response set above is sent
    }
    return true;
}
```

Forgetting `app()->setResponse()` before `return false` results in an empty or stale response being sent.

---

## Before vs. After Middleware

| Type | Runs | Purpose | Returns |
|------|------|---------|---------|
| Before | Before controller | Auth, rate limit, validation, request setup | `true` to continue, `false` to abort |
| After | After controller | Logging, response modification, cleanup | Almost always `true` |

**Naming convention:** `{Purpose}{Before|After}Middleware`

Examples: `AuthJwtBeforeMiddleware`, `RateLimitBeforeMiddleware`, `RequestIdBeforeMiddleware`, `ResponseLogAfterMiddleware`

---

## Pure Spin Style — Simple Cases

For straightforward middleware, extend `Spin\Core\Middleware` directly and use global helpers:

```php
class RequestIdBeforeMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        return true;
    }

    public function handle(array $args): bool
    {
        // Store a unique request ID for this request
        container('requestId', 'req_' . substr(bin2hex(random_bytes(8)), 0, 16));
        return true;
    }
}
```

Use `getRequest()`, `getResponse()`, `container()`, `logger()` — all global helpers available.

---

## JWT Auth Middleware Pattern (verified from AuthJwtBeforeMiddleware.php)

```php
class AuthJwtBeforeMiddleware extends AbstractSpinMiddleware
{
    protected function handleRequest($request, $response, callable $next)
    {
        $authHeader = $request->getHeaderLine('Authorization') ?? '';

        // Use substr, not str_replace, to avoid double-stripping
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return $response->withStatus(401)
                ->withHeader('WWW-Authenticate', 'Bearer realm="api"');
        }
        $token = substr($authHeader, 7);

        try {
            // 1. Structural validation — must have exactly 3 JWT parts
            $parts = explode('.', $token);
            if (count($parts) !== 3) throw new \Exception('Invalid JWT format');

            // 2. Decode header — assert RS256 before trusting anything
            $header = json_decode($this->decodeBase64Url($parts[0]), true);
            if (($header['alg'] ?? '') !== 'RS256') throw new \Exception('Unsupported algorithm');

            // 3. Verify RS256 signature with public key (cached in static)
            $signingInput = $parts[0] . '.' . $parts[1];
            $signature    = $this->decodeBase64Url($parts[2]);
            $result       = openssl_verify($signingInput, $signature, $this->getPublicKey(), 'sha256');
            if ($result !== 1) throw new \Exception('Signature invalid');

            // 4. Decode payload (only after signature verified)
            $payload = json_decode($this->decodeBase64Url($parts[1]), true);

            // 5. Verify exp and nbf
            if (!isset($payload['exp']) || $payload['exp'] < time()) throw new \Exception('Token expired');
            if (isset($payload['nbf']) && $payload['nbf'] > time()) throw new \Exception('Token not yet valid');

            // Store claims for downstream use
            container('jwt_claims', $payload);
            container('jwt_token', $token);

            return $next($request, $response);

        } catch (\Throwable $e) {
            logger()->warning('JWT validation failed', ['error' => $e->getMessage()]);
            return $response->withStatus(401)
                ->withHeader('WWW-Authenticate', 'Bearer realm="api"');
        }
    }
}
```

Claims stored: `container('jwt_claims')` → array, `container('jwt_token')` → string.

---

## Rate Limiting Middleware Pattern (verified from RateLimitBeforeMiddleware.php)

```php
class RateLimitBeforeMiddleware extends AbstractSpinMiddleware
{
    private const RATE_LIMIT_PER_MINUTE = 60;

    protected function handleRequest($request, $response, callable $next)
    {
        $jwtClaims = container('jwt_claims') ?? [];
        $userId    = $jwtClaims['sub'] ?? 'anonymous';

        $window   = (int)(time() / 60) * 60;  // floor to current minute
        $limitKey = "ratelimit:{$userId}:{$window}";

        try {
            $cache = cache('remote.redis');
            $count = (int)$cache->get($limitKey, 0);

            if ($count >= self::RATE_LIMIT_PER_MINUTE) {
                logger()->warning('Rate limit exceeded', ['user' => $userId, 'count' => $count]);
                return $response->withStatus(429)
                    ->withHeader('Retry-After', '60');
            }

            $cache->set($limitKey, $count + 1, 60);
            return $next($request, $response);

        } catch (\Throwable $e) {
            // Fail-open: if cache is unavailable, allow the request
            logger()->warning('Rate limit check failed', ['error' => $e->getMessage()]);
            return $next($request, $response);
        }
    }
}
```

Key points: fail-open on cache error (availability over security for rate limiting), per-user per-minute window.

---

## Registering Middleware in routes.json

All middleware is registered by FQCN (backslash-escaped in JSON):

```json
{
  "common": {
    "before": ["\\App\\Middlewares\\RequestIdBeforeMiddleware"],
    "after":  ["\\App\\Middlewares\\ResponseLogAfterMiddleware"]
  },
  "groups": [
    {
      "name": "API v1 Superadmin",
      "prefix": "/api/v1/admin",
      "before": [
        "\\App\\Middlewares\\AuthJwtBeforeMiddleware",
        "\\App\\Middlewares\\SuperadminBeforeMiddleware",
        "\\App\\Middlewares\\RateLimitBeforeMiddleware"
      ],
      "routes": [ ... ],
      "after": []
    }
  ]
}
```

Order in the array = execution order. For auth: JWT validation must run before role checks.

---

## After Middleware Pattern

After middleware always returns `true` (response is already set by controller):

```php
class ResponseLogAfterMiddleware extends Middleware
{
    public function initialize(array $args): bool { return true; }

    public function handle(array $args): bool
    {
        $status = getResponse()->getStatusCode();
        $level  = $status >= 500 ? 'error' : ($status >= 400 ? 'warning' : 'info');

        logger()->$level('Response', [
            'method' => getRequest()->getMethod(),
            'path'   => getRequest()->getUri()->getPath(),
            'code'   => $status,
            'rid'    => container('requestId'),
        ]);

        return true;  // After middleware should almost always return true
    }
}
```

---

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| `initialize()` missing `return bool` | Middleware `initialize()` MUST return `bool` |
| `return false` without `app()->setResponse(...)` | Always set a response before returning false |
| After middleware returning `false` | This stops response from being sent — after middleware should return `true` |
| Using `AbstractSpinMiddleware` for simple middleware | Use pure Spin style for simple tasks; adapter adds complexity |
| JWT: `str_replace('Bearer ', '', $header)` | Use `substr($authHeader, 7)` — str_replace double-strips if header contains "Bearer " twice |
| Rate limit failing closed on cache error | Fail open (allow request) — cache unavailability shouldn't block all traffic |
| Wrong execution order in group.before | Auth → Role check → Rate limit (JWT must be validated before role middleware reads claims) |
