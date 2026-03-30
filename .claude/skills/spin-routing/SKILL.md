---
name: spin-routing
description: Use when defining, modifying, or debugging routes in a Spin PHP application. Covers routes-{env}.json structure, route groups, path parameters, HTTP method matching, before/after middleware assignment, error handlers, and common 404/405 debugging. Always load when touching routes.json, adding or changing API endpoints, or when a route returns 404 or 405.
---

# Spin Routing — Reference

Spin uses JSON-based routing. Routes are defined in `src/app/Config/routes-{env}.json`, selected by the `ENVIRONMENT` variable in `.env`.

---

## File Structure

```
src/app/Config/
├── routes-dev.json     # Used when ENVIRONMENT=dev
└── routes-prod.json    # Used when ENVIRONMENT=prod
```

Both files must exist and be kept in sync. Changes to one must be reflected in the other (unless environment-specific).

---

## Top-Level Structure

```json
{
  "common": {
    "before": ["\\App\\Middlewares\\RequestIdBeforeMiddleware"],
    "after":  ["\\App\\Middlewares\\RequestIdAfterMiddleware", "\\App\\Middlewares\\ResponseLogAfterMiddleware"]
  },
  "groups": [ ... ],
  "errors": {
    "4xx": "\\App\\Controllers\\Api\\Error4xxController",
    "5xx": "\\App\\Controllers\\Api\\Error5xxController"
  }
}
```

| Key | Purpose |
|-----|---------|
| `common.before` | Runs on **every** request, before group middleware |
| `common.after` | Runs on **every** request, after the controller |
| `groups` | Array of route group objects |
| `errors.4xx` | Controller invoked for all 4xx HTTP errors |
| `errors.5xx` | Controller invoked for all 5xx HTTP errors |

---

## Route Group Anatomy

```json
{
  "name": "API v1 Superadmin",
  "prefix": "/api/v1/admin",
  "before": [
    "\\App\\Middlewares\\AuthJwtBeforeMiddleware",
    "\\App\\Middlewares\\SuperadminBeforeMiddleware",
    "\\App\\Middlewares\\RateLimitBeforeMiddleware"
  ],
  "routes": [
    {
      "methods": ["GET", "POST"],
      "path": "/associations",
      "handler": "\\App\\Controllers\\Api\\V1\\Admin\\AdminAssociationsController"
    },
    {
      "methods": ["GET", "PATCH", "DELETE"],
      "path": "/associations/{refId}",
      "handler": "\\App\\Controllers\\Api\\V1\\Admin\\AdminAssociationsController"
    }
  ],
  "after": []
}
```

| Field | Required | Notes |
|-------|----------|-------|
| `name` | yes | Human label for debugging |
| `prefix` | yes | URL prefix for all routes in this group |
| `before` | yes | Middleware run before controller (FQCN strings) |
| `routes` | yes | Array of route objects |
| `after` | yes | Middleware run after controller (FQCN strings) |

---

## Route Object

```json
{
  "methods": ["GET", "POST"],
  "path": "/items/{refId}",
  "handler": "\\App\\Controllers\\Api\\V1\\Admin\\AdminItemsController"
}
```

| Field | Notes |
|-------|-------|
| `methods` | Array of HTTP verbs. Use `[]` to match **all** methods. |
| `path` | Relative to group prefix. Use `{paramName}` for dynamic segments. |
| `handler` | Fully-qualified class name (FQCN) — always start with `\\` |

**Handler formats:**
- `"\\App\\Controllers\\Api\\V1\\Admin\\AdminFooController"` — Spin calls `handle()`, which dispatches to `handleGET/POST/...`
- `"\\App\\Controllers\\Api\\V1\\Admin\\AdminFooController@handleGET"` — Direct method call (bypass dispatch)
- `"UserController"` — Bare name resolves to `App\Controllers\UserController` (avoid — prefer FQCN)

---

## Path Parameters

Dynamic route segments use `{paramName}` syntax:

```json
{ "methods": ["GET"], "path": "/users/{refId}", "handler": "..." }
```

Accessed in the controller via the `$args` array:

```php
public function handleGET(array $args): Response
{
    $refId = $args['refId'];  // string from route
}
```

**Use `refId` (not `id`) for public-facing parameters.** Internal integer IDs must never be exposed in URLs.

Multiple params work the same:
```json
{ "path": "/chats/{chatRefId}/messages/{refId}", ... }
```
```php
$chatRefId = $args['chatRefId'];
$refId     = $args['refId'];
```

---

## Error Handlers

Error controllers must override `handle()` directly (not `handleGET`):

```php
class Error4xxController extends Controller
{
    public function handle(array $args): Response
    {
        $code = getResponse()->getStatusCode() ?: 404;
        return responseJson(['error' => 'Not Found', 'code' => $code], $code);
    }
}
```

Registered in routes.json:
```json
"errors": {
  "4xx": "\\App\\Controllers\\Api\\Error4xxController",
  "5xx": "\\App\\Controllers\\Api\\Error5xxController"
}
```

---

## Middleware Execution Order

For a request to `POST /api/v1/admin/associations`:

1. `common.before` middleware (e.g., `RequestIdBeforeMiddleware`)
2. Group `before` middleware (e.g., `AuthJwtBeforeMiddleware`, `SuperadminBeforeMiddleware`, `RateLimitBeforeMiddleware`)
3. Controller's `initialize()` → `handle()` → `handlePOST()`
4. Group `after` middleware
5. `common.after` middleware (e.g., `RequestIdAfterMiddleware`, `ResponseLogAfterMiddleware`)

If any `before` middleware returns `false`, the pipeline stops and the group/controller/after steps are skipped.

---

## Validation & Restart

After editing routes.json, always validate JSON before restarting:

```bash
# Validate JSON
node -e "JSON.parse(require('fs').readFileSync('src/app/Config/routes-dev.json','utf8')); console.log('OK')"

# Restart gateway
docker restart <container_name>
```

A malformed routes.json causes a 500 on every request with no useful error message.

---

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| `"methods": "GET"` (string) | Must be array: `"methods": ["GET"]` |
| `"methods": []` means nothing | `[]` means ALL methods — intentional |
| Missing leading `\\` in FQCN | `"\\App\\Controllers\\..."` (double backslash in JSON) |
| Route defined but 404 on request | Check prefix + path concatenation; restart container |
| Route defined but 405 on request | Add the missing HTTP verb to `"methods"` array |
| Same path defined in two groups | FastRoute uses first match; order groups carefully |
| Forgetting to edit both dev and prod | Changes to one file must be mirrored in the other |
| Adding route but not restarting | Spin reads routes at boot; always restart after changes |
