---
name: spin-cache
description: Use when implementing or configuring caching in a Spin PHP application. Covers the cache() helper, PSR-16 CacheInterface (get/set/delete/clear/has), named cache adapters (APCu for local in-process, Redis for shared/distributed), TTL strategies, cache key design, the cache-aside pattern, and cache configuration in config-{env}.json. Always load when adding any cache layer to controllers, services, or middleware, or when debugging cache misses or stale data.
---

# Spin Caching — Reference

---

## cache() Helper

Returns a named cache adapter. Name maps to a key in `config-{env}.json` under `caches`:

```php
$apcu  = cache('local.apcu');    // APCu — in-process, per worker
$redis = cache('remote.redis');  // Redis — shared across all workers
```

Both return a PSR-16 `CacheInterface` instance. If the adapter is unavailable, `cache()` returns `null` — null-check when availability matters.

---

## PSR-16 Interface

```php
// Get value (returns $default if key missing or expired)
$value = $cache->get('my-key', null);

// Set value with TTL in seconds (null = indefinite)
$cache->set('my-key', $value, 300);     // 5 minutes
$cache->set('my-key', $value, null);    // no expiry

// Delete
$cache->delete('my-key');

// Check existence (without fetching)
$exists = $cache->has('my-key');

// Clear entire cache
$cache->clear();

// Bulk operations
$cache->getMultiple(['key1', 'key2'], null);
$cache->setMultiple(['key1' => $v1, 'key2' => $v2], 300);
$cache->deleteMultiple(['key1', 'key2']);
```

---

## Cache-Aside Pattern

The standard pattern for caching expensive operations:

```php
$cache = cache('local.apcu');
$key   = "users:{$id}:profile";

$profile = $cache->get($key, null);
if ($profile === null) {
    // Cache miss — fetch from source
    $profile = $this->userRepo->getById($id);
    if ($profile !== null) {
        $cache->set($key, $profile, 300);  // cache for 5 minutes
    }
}

return $profile;
```

**Distinguish null-as-default from null-as-miss:**
```php
// If your data can legitimately be null, use a sentinel:
$cached = $cache->get($key, '__MISS__');
if ($cached === '__MISS__') {
    $cached = $this->fetchExpensiveData();
    $cache->set($key, $cached, 300);
}
return $cached;
```

---

## APCu Adapter (`local.apcu`)

**Use for:** Config caches, per-request memoization, computed values that don't need sharing.

**Properties:**
- In-process only — not shared across PHP workers or server instances
- Very fast (in-memory, same process)
- Lost on process restart
- Each PHP-FPM worker has its own APCu cache

**Config:**
```json
"local.apcu": {
  "adapter": "APCu",
  "class": "\\Spin\\Cache\\Adapters\\Apcu",
  "options": {}
}
```

**Do not use for:** Rate limiting, session data, anything that must be consistent across workers or servers.

---

## Redis Adapter (`remote.redis`)

**Use for:** Rate limiting, distributed locks, shared session data, anything requiring consistency across workers/instances.

**Properties:**
- Shared across all PHP workers and server instances
- Survives process restarts (persistent)
- Network latency (~1ms local)
- Available via `cache('remote.redis')`

**Config:**
```json
"remote.redis": {
  "adapter": "Redis",
  "class": "\\Spin\\Cache\\Adapters\\Redis",
  "options": {
    "host": "${env:REDIS_HOST}",
    "port": "${env:REDIS_PORT}"
  }
}
```

---

## Adapter Selection Guide

| Use case | Adapter | Reason |
|----------|---------|--------|
| Rate limiting | `remote.redis` | Must be shared across workers |
| JWT public key (per-process) | static var or `local.apcu` | Per-process is fine |
| Config/lookup data | `local.apcu` | Fast; reloaded on restart is OK |
| API response caching (shared) | `remote.redis` | Consistent across instances |
| Per-request memoization | Static variable | No network call; dies with request |
| Distributed lock | `remote.redis` | Requires cross-process coordination |

---

## TTL Guidelines

| Data type | TTL | Rationale |
|-----------|-----|-----------|
| API aggregations, stats | 60–300s | Fresh enough, saves DB load |
| Config / lookup tables | 300–3600s | Changes rarely, cache aggressively |
| User profile data | 60–120s | Balances freshness vs. performance |
| Rate limit windows | 60s (= window) | Must expire at end of window |
| Immutable data (content by ID) | 3600s+ or null | Never changes — cache long |

---

## Cache Key Design

Use namespaced, versioned keys to avoid collisions and enable easy invalidation:

```php
// Namespaced by entity and ID
"users:{$userId}:profile"
"associations:{$assocId}:settings"

// With version prefix for cache busting across deploys
"v1:users:{$userId}:profile"

// Rate limit: per-user per-minute window
"ratelimit:{$userId}:{$windowTimestamp}"
```

**Avoid:**
- Generic keys like `"cache"` or `"data"` — collision-prone
- Keys with user-supplied input without sanitization — injection risk
- Extremely long keys — Redis has a 512MB key limit (practically, keep keys short)

---

## Rate Limiting Pattern (verified from RateLimitBeforeMiddleware)

```php
$userId   = (container('jwt_claims') ?? [])['sub'] ?? 'anonymous';
$window   = (int)(time() / 60) * 60;   // floor to minute boundary
$limitKey = "ratelimit:{$userId}:{$window}";
$limit    = 60;  // requests per minute

try {
    $cache = cache('remote.redis');
    $count = (int)$cache->get($limitKey, 0);

    if ($count >= $limit) {
        // Return 429 Too Many Requests
    }

    $cache->set($limitKey, $count + 1, 60);  // TTL = window duration

} catch (\Throwable $e) {
    // Fail open — if Redis is down, don't block all requests
    logger()->warning('Rate limit check failed', ['error' => $e->getMessage()]);
}
```

**Fail-open philosophy:** Cache unavailability should not cause requests to fail. For rate limiting, the risk of a few extra requests during a Redis outage is acceptable.

---

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| Using APCu for cross-worker shared state | APCu is per-process — use Redis for shared state |
| Not setting a TTL (indefinite growth) | Set appropriate TTL unless data is truly immutable |
| Cache key collision | Use namespaced keys: `"{entity}:{id}:{field}"` |
| `$cache->get($key, null)` with nullable data | Use a sentinel value like `'__MISS__'` |
| Failing closed on cache errors | For rate limiting/non-critical caching: fail open |
| Caching mutable user data with long TTL | Short TTL or explicit invalidation on write |
| Not checking `cache()` return for null | `cache()` can return null if adapter not configured |
