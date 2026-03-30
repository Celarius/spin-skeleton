---
name: spin-controllers
description: Use when writing or modifying Spin PHP controllers. Covers the AbstractController lifecycle (initialize/handle/handleGET etc.), parameter access patterns ($args for route params, queryParam() for query strings, decodeJsonBody()/$this->body for payloads), the verify*/handle* guard pattern, response helpers, try/catch+logger error handling, and the AbstractAdminController/AbstractProxyController class hierarchy. Always load when implementing controller methods, debugging 405 errors, or reviewing controller code.
---

# Spin Controllers — Reference

---

## Class Hierarchy

```
Spin\Core\Controller                     (framework base)
  └── App\Controllers\AbstractController  (adds decodeJsonBody + $body)
        ├── App\Controllers\Api\V1\Admin\AbstractAdminController
        │     └── AdminFooController
        └── App\Controllers\Api\V1\Board\AbstractProxyController
              └── BoardFooController
```

---

## Lifecycle

1. Route matched → Spin calls `$controller->initialize($args)` (optional override)
2. Spin calls `$controller->handle($args)` (framework dispatch method)
3. `handle()` reads HTTP verb and delegates to `handleGET/POST/PATCH/DELETE/...`
4. Unimplemented verb methods return `response('', 405)` by default

```php
// Override initialize for pre-flight checks (auth, resource existence, etc.)
public function initialize(array $args): bool
{
    // return false → Spin skips handle() and uses whatever response is set
    // return true → continue to handle()
    return true;
}
```

**Note:** Controller `initialize()` has no required return type in the framework base. However, it must return `bool` in practice if you override it. Middleware `initialize()` MUST return `bool` (strict contract).

---

## Parameter Access — Reference Table

| Source | How to access | Notes |
|--------|--------------|-------|
| Route path params | `$args['refId']` | Populated by FastRoute from `{refId}` in route path |
| Query string params | `\queryParam('page')` | Returns `null` if missing |
| All query params | `\queryParams()` | Returns associative array |
| JSON request body | `$this->body['field']` | Call `decodeJsonBody()` first; reads from `$this->body` |
| POST form params | `\postParam('field')` | For form-encoded requests |
| Cookie | `\cookieParam('name')` | Returns `null` if missing |

**Never use** `$_GET`, `$_POST`, `$_REQUEST`, or `$request->get(':param')` — these bypass the PSR-7 pipeline.

---

## decodeJsonBody()

Defined in `AbstractController`. Validates Content-Type and decodes the JSON body into `$this->body`.

```php
protected function decodeJsonBody(): ?Response
```

- Returns `null` on success (body available in `$this->body`)
- Returns an error `Response` (400) if Content-Type is not `application/json`
- Returns an error `Response` (400) if JSON is malformed
- Idempotent — safe to call multiple times (reads stream only once)

---

## verify*/handle* Guard Pattern

Every mutating method (POST, PATCH, DELETE) uses a private `verify*` method that runs validation before business logic. This keeps the handle method clean and stops early on bad input.

```php
private function verifyPOST(array $args): ?Response
{
    if ($r = $this->decodeJsonBody()) return $r;          // Content-Type + JSON check

    if (empty($this->body)) {
        return \responseJsonError('Bad request', 'Payload is mandatory', 400);
    }

    // Add field-level validation here
    if (empty($this->body['name'] ?? '')) {
        return \responseJsonError('Bad request', "'name' is required", 400);
    }

    return null;  // null = validation passed
}

public function handlePOST(array $args): Response
{
    try {
        if ($r = $this->verifyPOST($args)) return $r;   // Early return on validation failure

        // Business logic here — $this->body is populated and validated

    } catch (\Throwable $e) {
        \logger()->critical('Exception', [
            'message'   => 'Failed to create item',
            'exception' => $e->getMessage(),
            'requestId' => container('requestId'),
            'trace'     => $e->getTraceAsString()
        ]);
        return responseJsonError('Internal server error', 'Failed to create item', 500);
    }
}
```

Pattern applies to all mutating verbs: `verifyPOST`, `verifyPATCH`, `verifyDELETE`.

For `verifyGET` — only add it when route parameters must be validated before hitting the DB (e.g., required `refId`, UUID format check). Most GET handlers can skip it.

---

## Standard Error Handling

Every `handle*` method wraps business logic in try/catch:

```php
public function handleGET(array $args): Response
{
    try {
        // business logic

    } catch (\Throwable $e) {
        \logger()->critical('Exception', [
            'message'   => 'Human-readable description for logs',
            'exception' => $e->getMessage(),
            'requestId' => container('requestId'),
            'trace'     => $e->getTraceAsString()
        ]);
        return responseJsonError('Internal server error', 'Public-facing message', 500);
    }
}
```

**Rules:**
- Always catch `\Throwable` (not just `\Exception`) — catches PHP errors too
- Always include `requestId` in the log context for trace correlation
- Keep `message` in logs internal (full details); keep message in `responseJsonError` vague and public-safe
- Use `logger()->critical()` for unexpected exceptions; `logger()->warning()` for expected validation failures

---

## Response Helpers Quick-Reference

```php
// Success — 200 OK
return responseJson(['data' => $items]);

// Created — 201
return responseJson(['data' => $item], 201);

// No content — 204 (standard for DELETE)
return response('', 204);

// Error responses — always use responseJsonError for consistency
return responseJsonError('Not Found',     'Item not found',        404);
return responseJsonError('Bad request',   'Payload is mandatory',  400);
return responseJsonError('Forbidden',     'Insufficient access',   403);
return responseJsonError('Server Error',  'Failed to process',     500);
```

`responseJsonError` is defined in `App\Globals.php` (not the framework). It produces:
```json
{ "result": "ERROR", "code": null, "title": "...", "message": "...", "rid": "req_..." }
```

---

## AbstractAdminController Extras

Defined in `App\Controllers\Api\V1\Admin\AbstractAdminController`.

```php
// Build a paginated response envelope
$this->paginatedResponse(array $items, int $total, int $page, int $per_page): array
// Returns: { status, data, pagination: { page, per_page, total, total_pages, has_more } }

// Extract and validate pagination query params (page, per_page)
$this->getPaginationParams(int $default_per_page = 50): array
// Returns: ['page' => int, 'per_page' => int]  — page >= 1, per_page <= 250

// Extract allowed filter params from query string
$this->getFilterParams(array $allowed_filters): array
// Returns: ['fieldName' => 'value', ...] — only includes params that are non-null

// Extract sort params from query string
$this->getSortParams(string $default_sort = 'created_at', array $allowed_sorts = []): array
// Returns: ['field' => string, 'direction' => 'ASC'|'DESC']

// Validate required fields in a data array
$this->validateRequired(array $data, array $required_fields): array
// Returns: [] on success, ['field' => 'error message'] on failure
```

Typical paginated GET:
```php
public function handleGET(array $args): Response
{
    try {
        ['page' => $page, 'per_page' => $perPage] = $this->getPaginationParams();
        $filters = $this->getFilterParams(['status', 'search']);

        $items = $this->itemRepo->getAll($filters, $page, $perPage);
        $total = $this->itemRepo->countTotal($filters);

        return responseJson($this->paginatedResponse($items, $total, $page, $perPage));

    } catch (\Throwable $e) {
        // ... standard catch block
    }
}
```

---

## AbstractProxyController Extras

Defined in `App\Controllers\Api\V1\Board\AbstractProxyController`. Same pagination/filter/sort helpers as Admin, plus:

```php
// Get association (tenant) ID from JWT-populated container
protected function getAssociationId(Request $request): ?int

// Get JWT claims from container
protected function getJwtClaims(Request $request): array
```

**Every Board controller method MUST scope to the current association.** Start every `handle*` method with:

```php
public function handleGET(array $args): Response
{
    $associationId = $this->getAssociationId(getRequest());
    if ($associationId === null) {
        return responseJsonError('Forbidden', 'Missing association context', 403);
    }

    try {
        // All DB queries MUST include AND association_id = :assocId
    }
}
```

---

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| Returning plain array from controller | Always return `responseJson(...)` or `response(...)` |
| `$this->respond(...)` wrapper | Call `responseJson()` / `responseJsonError()` directly |
| `$_GET[':paramName']` for route params | Route params are in `$args['paramName']` |
| POST without `verifyPOST()` guard | All POST/PATCH must call `decodeJsonBody()` and validate |
| Missing `try/catch` in handle methods | All handle methods must catch `\Throwable` |
| Board controller missing `getAssociationId()` | Start every Board handle* method with the null-check pattern |
| `initialize()` not returning bool | Always `return true` (or `false` to abort) from `initialize()` |
| Using `responseJson` for error responses | Use `responseJsonError` for consistency — it adds the `rid` field |
