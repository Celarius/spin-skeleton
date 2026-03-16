# Testing Guide

Spin Skeleton uses PHPUnit for testing. This guide covers setup, writing tests, and best practices.

## Running Tests

### Basic Test Execution

```bash
composer test
```

Or run PHPUnit directly:

```bash
./vendor/bin/phpunit
```

### Run Specific Test File

```bash
./vendor/bin/phpunit tests/Controllers/ApiControllerTest.php
```

### Run Specific Test Method

```bash
./vendor/bin/phpunit --filter testUserCreation
```

### Run Multiple Methods

```bash
./vendor/bin/phpunit --filter "testUser(Creation|Deletion)"
```

### Verbose Output

```bash
composer test -- --verbose
# or
./vendor/bin/phpunit --verbose
```

Shows each test as it runs:

```
PHPUnit 12.0.0 by Sebastian Bergmann and contributors.

E ... . F ...                                              11 / 11 (100%)

Time: 0.456 seconds, Memory: 10.50 MB

FAILURES!
Tests run: 11, Failures: 1, Errors: 1
```

### Coverage Reporting

Generate HTML coverage report:

```bash
./vendor/bin/phpunit --coverage-html coverage/
```

Opens `coverage/index.html` in your browser to see which code is covered by tests.

Coverage with text output:

```bash
./vendor/bin/phpunit --coverage-text
```

## Test Structure

Tests mirror the `src/app` structure:

```
tests/
├── Controllers/
│   ├── Api/
│   │   └── PostControllerTest.php
│   ├── PageControllerTest.php
│   └── IndexControllerTest.php
├── Middlewares/
│   ├── AuthBeforeMiddlewareTest.php
│   └── LogResponseAfterMiddlewareTest.php
├── Models/
│   └── UserTest.php
├── ApplicationTest.php
├── bootstrap.php
└── phpunit.xml
```

Create test file alongside code:

```
src/app/Controllers/Api/PostController.php    → tests/Controllers/Api/PostControllerTest.php
src/app/Middlewares/AuthBeforeMiddleware.php  → tests/Middlewares/AuthBeforeMiddlewareTest.php
```

## Writing Controller Tests

### Basic Test Class

```php
<?php declare(strict_types=1);

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;

class HealthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        // Code that runs before each test
        // Initialize app, database, fixtures, etc.
    }

    protected function tearDown(): void
    {
        // Code that runs after each test
        // Cleanup, reset state, etc.
    }

    public function testHealthEndpointReturnsOk(): void
    {
        // Arrange
        $expected = ['status' => 'OK'];

        // Act
        // Call controller or endpoint

        // Assert
        $this->assertEquals($expected, $result);
    }
}
```

### Controller Test with Database

```php
<?php declare(strict_types=1);

namespace Tests\Controllers\Api;

use PHPUnit\Framework\TestCase;

class PostControllerTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        // Set environment to unittest (uses SQLite in-memory)
        $_ENV['ENVIRONMENT'] = 'unittest';

        // Create test tables
        $this->db = app()->getConnection('default');
        $this->db->exec('
          CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
          )
        ');

        // Insert test fixtures
        $stmt = $this->db->prepare('INSERT INTO posts (title, content) VALUES (?, ?)');
        $stmt->execute(['Test Post', 'This is a test post']);
    }

    protected function tearDown(): void
    {
        // Drop tables after each test
        $this->db->exec('DROP TABLE IF EXISTS posts');
    }

    public function testGetPostsReturnsJsonArray(): void
    {
        // Arrange
        $controller = new \App\Controllers\Api\PostController();

        // Act
        $response = $controller->handleGET([]);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeader('Content-Type')[0]);

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($body['data']);
        $this->assertCount(1, $body['data']);
    }

    public function testGetSinglePostReturnsData(): void
    {
        // Arrange
        $postId = 1;
        $controller = new \App\Controllers\Api\PostController();

        // Act
        $response = $controller->handleGET_Detail(['id' => $postId]);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('Test Post', $body['data']['title']);
    }

    public function testGetNonExistentPostReturns404(): void
    {
        // Arrange
        $controller = new \App\Controllers\Api\PostController();

        // Act
        $response = $controller->handleGET_Detail(['id' => 999]);

        // Assert
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCreatePostInsertsToDatabaseAndReturnsId(): void
    {
        // Arrange
        $controller = new \App\Controllers\Api\PostController();
        $request = getRequest()
            ->withMethod('POST')
            ->withHeader('Content-Type', 'application/json')
            ->withBody('{"title":"New Post","content":"New content"}');

        // Act
        $response = $controller->handlePOST([]);

        // Assert
        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertNotEmpty($body['id']);

        // Verify in database
        $stmt = $this->db->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$body['id']]);
        $post = $stmt->fetchAssoc();
        $this->assertEquals('New Post', $post['title']);
    }

    public function testUpdatePostModifiesDatabase(): void
    {
        // Arrange
        $controller = new \App\Controllers\Api\PostController();

        // Act
        $response = $controller->handlePUT(['id' => 1]);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());

        // Verify update
        $stmt = $this->db->prepare('SELECT title FROM posts WHERE id = 1');
        $stmt->execute();
        $post = $stmt->fetchAssoc();
        $this->assertEquals('Updated Title', $post['title']);
    }

    public function testDeletePostRemovesFromDatabase(): void
    {
        // Arrange
        $controller = new \App\Controllers\Api\PostController();

        // Act
        $response = $controller->handleDELETE(['id' => 1]);

        // Assert
        $this->assertEquals(204, $response->getStatusCode());

        // Verify deletion
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM posts WHERE id = 1');
        $stmt->execute();
        $result = $stmt->fetchAssoc();
        $this->assertEquals(0, $result['count']);
    }
}
```

## Writing Middleware Tests

```php
<?php declare(straight_types=1);

namespace Tests\Middlewares;

use PHPUnit\Framework\TestCase;
use App\Middlewares\AuthBeforeMiddleware;

class AuthBeforeMiddlewareTest extends TestCase
{
    private AuthBeforeMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new AuthBeforeMiddleware();
        $this->middleware->initialize([]);
    }

    public function testInitializeReturnsTrue(): void
    {
        // Arrange & Act
        $result = $this->middleware->initialize([]);

        // Assert
        $this->assertTrue($result);
    }

    public function testHandleWithValidTokenReturnsTrue(): void
    {
        // Arrange
        $token = 'valid_token_123';
        $request = getRequest()->withHeader('Authorization', "Bearer $token");

        // Act
        $result = $this->middleware->handle([]);

        // Assert
        $this->assertTrue($result);
    }

    public function testHandleWithoutAuthHeaderReturnsFalse(): void
    {
        // Arrange
        $request = getRequest();

        // Act
        $result = $this->middleware->handle([]);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals(401, getResponse()->getStatusCode());
    }

    public function testHandleWithInvalidTokenReturnsFalse(): void
    {
        // Arrange
        $request = getRequest()->withHeader('Authorization', 'Bearer invalid_token');

        // Act
        $result = $this->middleware->handle([]);

        // Assert
        $this->assertFalse($result);
    }
}
```

## Test Utilities and Base Classes

Create a base test class with common setup:

```php
<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class ControllerTestCase extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        $_ENV['ENVIRONMENT'] = 'unittest';
        $this->db = app()->getConnection('default');
    }

    protected function tearDown(): void
    {
        // Reset state
        if ($this->db) {
            $this->db->exec('PRAGMA foreign_keys = OFF');
        }
    }

    /**
     * Create test fixtures
     */
    protected function createTestUser(string $email = 'test@example.com'): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (email, name) VALUES (?, ?)');
        $stmt->execute([$email, 'Test User']);
        return (int) $this->db->lastInsertId();
    }

    protected function createTestPost(int $userId, string $title = 'Test'): int
    {
        $stmt = $this->db->prepare('INSERT INTO posts (user_id, title) VALUES (?, ?)');
        $stmt->execute([$userId, $title]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Assert helpers
     */
    protected function assertJsonResponse(int $expectedStatus, string $response): array
    {
        $this->assertEquals($expectedStatus, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeader('Content-Type')[0]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
```

Use in tests:

```php
<?php declare(strict_types=1);

namespace Tests\Controllers\Api;

use Tests\ControllerTestCase;

class UserControllerTest extends ControllerTestCase
{
    public function testGetUserReturnsUserData(): void
    {
        // Arrange
        $userId = $this->createTestUser('john@example.com');
        $controller = new \App\Controllers\Api\UserController();

        // Act
        $response = $controller->handleGET(['id' => $userId]);

        // Assert
        $body = $this->assertJsonResponse(200, $response);
        $this->assertEquals('john@example.com', $body['data']['email']);
    }
}
```

## Database Testing with SQLite In-Memory

Configuration for tests (`config-unittest.json`):

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

Benefits of in-memory SQLite:
- No disk I/O — tests run fast
- Isolated per test — no state leakage
- No cleanup needed — disposed after each test
- Full SQL support — same as production database

Test bootstrap (`tests/bootstrap.php`):

```php
<?php declare(strict_types=1);

// Set environment for tests
$_ENV['ENVIRONMENT'] = 'unittest';

// Load application
require __DIR__ . '/../src/public/bootstrap.php';

// Create test database tables
$db = app()->getConnection('default');
$db->exec(file_get_contents(__DIR__ . '/fixtures/schema.sql'));
```

## Testing Best Practices

### 1. Clear Test Names

```php
// Good
public function testCreatePostWithValidDataReturnsIdAndStatus201(): void

// Bad
public function testPost(): void
```

### 2. Arrange-Act-Assert (AAA) Pattern

```php
public function testUserDeletionRemovesFromDatabase(): void
{
    // Arrange — Setup test fixtures
    $userId = $this->createTestUser();

    // Act — Execute behavior
    $controller = new UserController();
    $response = $controller->handleDELETE(['id' => $userId]);

    // Assert — Verify results
    $this->assertEquals(204, $response->getStatusCode());
    $this->assertEquals(0, $this->countUsers());
}
```

### 3. One Assertion Per Test (When Possible)

```php
// Good — focused test
public function testCreatePostReturnsStatusCreated(): void
{
    $response = $this->controller->handlePOST([]);
    $this->assertEquals(201, $response->getStatusCode());
}

// Less ideal — multiple assertions
public function testCreatePost(): void
{
    $response = $this->controller->handlePOST([]);
    $this->assertEquals(201, $response->getStatusCode());
    $this->assertStringContainsString('application/json', $response->getHeader('Content-Type')[0]);
    $body = json_decode($response->getBody()->getContents(), true);
    $this->assertNotEmpty($body['id']);
}
```

### 4. Test Independence

```php
// Good — each test is independent
public function testGetUserReturnsUserData(): void
{
    $userId = $this->createTestUser();
    // ...
}

public function testUpdateUserModifiesData(): void
{
    $userId = $this->createTestUser();
    // ...
}

// Bad — test depends on execution order
private $userId;

public function testCreateUser(): void
{
    $this->userId = $this->createTestUser();
}

public function testGetUser(): void
{
    $this->assertNotNull($this->userId); // Fails if testCreateUser doesn't run first
}
```

### 5. Mock External Dependencies

```php
// Test with external API mocked
public function testProcessPaymentCallsGateway(): void
{
    // Arrange
    $mockGateway = $this->createMock(PaymentGateway::class);
    $mockGateway->expects($this->once())
        ->method('charge')
        ->with(100.00, 'card-token');

    // Act
    $processor = new PaymentProcessor($mockGateway);
    $processor->process(100.00, 'card-token');

    // Assert — mocking handles verification
}
```

### 6. Use Fixtures for Complex Data

```php
// Create fixtures file: tests/fixtures/users.json
[
    { "id": 1, "email": "alice@example.com", "name": "Alice" },
    { "id": 2, "email": "bob@example.com", "name": "Bob" }
]

// Load in tests
protected function loadFixtures(): void
{
    $users = json_decode(file_get_contents(__DIR__ . '/fixtures/users.json'), true);
    foreach ($users as $user) {
        $this->createUser($user);
    }
}
```

## Common Assertions

```php
// Equality
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual); // strict equality (===)
$this->assertNotEquals($expected, $actual);

// Booleans
$this->assertTrue($value);
$this->assertFalse($value);

// Null/Empty
$this->assertNull($value);
$this->assertNotNull($value);
$this->assertEmpty($array);
$this->assertNotEmpty($array);

// Arrays
$this->assertArrayHasKey('key', $array);
$this->assertArrayNotHasKey('key', $array);
$this->assertCount(5, $array);

// Strings
$this->assertStringContainsString('substr', $string);
$this->assertStringNotContainsString('substr', $string);
$this->assertStringStartsWith('prefix', $string);
$this->assertStringEndsWith('suffix', $string);

// Collections
$this->assertContains($value, $array);
$this->assertNotContains($value, $array);

// Exceptions
$this->expectException(InvalidArgumentException::class);
$this->functionThatThrows();
```

## See Also

- [Getting Started Guide](getting-started.md)
- [Configuration Guide](configuration.md)
- [Database Connections Guide](database-connections.md)
- [Controllers & Middleware Guide](controllers-and-middleware.md)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
