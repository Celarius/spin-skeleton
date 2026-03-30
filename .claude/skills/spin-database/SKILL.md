---
name: spin-database
description: Use when writing database queries, managing PDO connections, implementing the repository pattern, or handling database transactions in a Spin PHP application. Covers the db() helper, PDO prepared statements, fetch patterns, transactions, and the BaseRepository class (getAll, countTotal, getById, getByRefId, create, update, softDelete, hardDelete, exists, generateRefId). Always load when writing SQL, working with repositories, or debugging database errors. For multi-tenant scoping and zero-knowledge patterns, also see mariadb-smartbrf skill.
---

# Spin Database — Reference

---

## db() Helper

Returns a named PDO connection. Connection name maps to a key in `config-{env}.json` under `connections`:

```php
$conn = db('main');   // returns PDO instance or null
```

**Always null-check** — returns `null` if the connection is unavailable:

```php
$conn = db('main');
if ($conn === null) {
    return responseJsonError('Server Error', 'Database unavailable', 503);
}
```

---

## Prepared Statements

Never use string interpolation in SQL. Always use prepared statements:

```php
// Positional params — good for simple queries
$stmt = $conn->prepare('SELECT * FROM items WHERE id = ? AND deleted_at IS NULL');
$stmt->execute([$id]);

// Named params — good for complex queries with many params
$stmt = $conn->prepare('SELECT * FROM items WHERE association_id = :assocId AND status = :status AND deleted_at IS NULL');
$stmt->execute([':assocId' => $assocId, ':status' => $status]);
```

---

## Fetch Patterns

```php
// Single row — returns array or null (never false)
$row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

// Collection — returns array (never false)
$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

// Scalar count
$result = $stmt->fetch(\PDO::FETCH_ASSOC);
$count  = (int)($result['total'] ?? 0);
```

Use `?: null` (not `=== false`) to normalize the false-on-miss from `fetch()`.

---

## INSERT + lastInsertId

```php
$stmt = $conn->prepare('INSERT INTO items (refId, name, association_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$refId, $name, $assocId, date('Y-m-d H:i:s.000'), date('Y-m-d H:i:s.000')]);
$newId = (int)$conn->lastInsertId();
```

Cast `lastInsertId()` to `(int)` — it returns a string.

---

## Transaction Pattern

```php
$conn = db('main');
$conn->beginTransaction();
try {
    $conn->prepare('UPDATE accounts SET balance = balance - ? WHERE id = ?')->execute([$amount, $fromId]);
    $conn->prepare('UPDATE accounts SET balance = balance + ? WHERE id = ?')->execute([$amount, $toId]);
    $conn->commit();
} catch (\Throwable $e) {
    $conn->rollBack();
    logger()->critical('Transaction failed', [
        'message'   => 'Balance transfer failed',
        'exception' => $e->getMessage(),
        'requestId' => container('requestId'),
    ]);
    throw $e;  // or return error response
}
```

`ATTR_AUTOCOMMIT = false` in config means each request gets a clean transaction state.

---

## BaseRepository

Abstract base class for all repositories in `App\Repositories\`. Verified from `BaseRepository.php`.

### Properties

```php
protected string $table    = '';       // MUST override in subclass
protected string $primaryKey = 'id';
protected string $refIdColumn = 'refId';
protected bool   $useSoftDeletes = true;
```

### conn() — Connection Access

```php
protected function conn(): \PDO
// Calls db('main'); throws RuntimeException if connection is null
// Use conn() in repository methods instead of db() directly
```

### Methods

```php
// Get paginated list with optional filters
getAll(array $filters = [], int $page = 1, int $perPage = 50): array

// Count total records matching filters (for pagination)
countTotal(array $filters = []): int

// Get single record by primary key (returns null if not found/deleted)
getById(int|string $id): ?array

// Get single record by refId (for public APIs)
getByRefId(string $refId): ?array

// Create record — auto-adds created_at, updated_at, refId
create(array $data): array   // returns the created record

// Update record — auto-updates updated_at; ignores id/timestamps in $data
update(int|string $id, array $data): array   // returns updated record

// Soft delete — sets deleted_at = NOW(3)
softDelete(int|string $id): bool

// Hard delete — permanent removal
hardDelete(int|string $id): bool

// Check if record exists (respects soft deletes)
exists(int|string $id): bool

// Generate a unique refId: 'ref_' + 28 hex chars
generateRefId(): string   // protected — used internally by create()
```

### Example Repository

```php
<?php declare(strict_types=1);
namespace App\Repositories;

class ItemRepository extends BaseRepository
{
    protected string $table = 'items';

    // Custom query beyond base CRUD
    public function getByAssociation(int $associationId, int $page = 1, int $perPage = 50): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE association_id = :assocId AND deleted_at IS NULL
                ORDER BY created_at DESC
                LIMIT :offset, :limit";

        $stmt = $this->conn()->prepare($sql);
        $stmt->execute([
            ':assocId' => $associationId,
            ':offset'  => ($page - 1) * $perPage,
            ':limit'   => $perPage,
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
```

### Injecting into Controllers

```php
class AdminItemsController extends AbstractAdminController
{
    private ItemRepository $itemRepo;

    public function __construct()
    {
        parent::__construct();
        $this->itemRepo = new ItemRepository();
    }
}
```

---

## Soft Delete Convention

Tables with `$useSoftDeletes = true` use a `deleted_at` column:

- Active records: `deleted_at IS NULL`
- Deleted records: `deleted_at = NOW(3)` (millisecond precision)
- `getAll`, `getById`, `getByRefId`, `exists` all automatically filter `deleted_at IS NULL`
- `softDelete()` sets `deleted_at = NOW(3)` — record remains in DB

For custom queries, always add `AND deleted_at IS NULL` unless you explicitly want deleted records.

---

## refId Pattern

- Never expose auto-increment `id` in API responses or URL paths
- Use `refId` (string) for all external references
- Format: `ref_` + 28 hex characters (e.g., `ref_a3f2b891c04d7e5f2a1b3d4e`)
- `BaseRepository::create()` auto-generates `refId` if not provided
- Store `id` for internal JOINs; expose only `refId` externally

---

## Timestamp Handling

- Column type: `DATETIME(3)` (millisecond precision)
- `create()` sets `created_at` and `updated_at` to `date('Y-m-d H:i:s.000')`
- `update()` sets `updated_at` automatically
- `softDelete()` sets `deleted_at = NOW(3)`
- Never manually manage these timestamps when using `BaseRepository`

---

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| `db()` return not null-checked | Always check `if ($conn === null)` before using |
| `fetch()` result used without `?: null` | `fetch()` returns `false` on miss — normalize with `?: null` |
| `lastInsertId()` not cast to int | `(int)$conn->lastInsertId()` — it returns a string |
| String interpolation in SQL | Always use prepared statements with `?` or `:param` |
| Transaction without `rollBack()` in catch | Always call `$conn->rollBack()` in the catch block |
| Exposing `id` in API response | Use `refId` for external identifiers; never expose auto-increment IDs |
| Custom query missing `deleted_at IS NULL` | Add to every custom query unless you want deleted records |
