# Spin-Skeleton Improvements (Ideas for Future Work)

This document lists non-breaking enhancements to consider in future iterations. These are suggestions, not required changes.

---

## Models & ORM Patterns

Add a models layer to encapsulate database interactions and business logic. Consider implementing both Active Record and Repository patterns to give developers flexibility in how they structure data access.

**Suggested structure:**
```
src/app/Models/
  ├── BaseModel.php              # Common query, save, delete methods
  ├── Repositories/
  │   ├── UserRepository.php     # Repository pattern example
  │   └── AbstractRepository.php  # Base repository class
  └── User.php                    # Active Record style model
```

**Example BaseModel:**
```php
abstract class BaseModel {
    protected string $table;
    protected PDO $pdo;

    public function find(int $id): ?array { /* ... */ }
    public function all(): array { /* ... */ }
    public function save(): bool { /* ... */ }
    public function delete(): bool { /* ... */ }
}
```

---

## Database Migrations

Implement a lightweight migration system for schema versioning and evolution. Store migration files in version control and track applied migrations in the database.

**Console commands:**
```bash
php console migrate:create CreateUsersTable
php console migrate:up
php console migrate:down
php console migrate:status
```

**Suggested structure:**
```
src/app/Migrations/
  ├── Migration.php              # Base migration class
  ├── 2026_03_16_001_CreateUsersTable.php
  └── 2026_03_16_002_AddEmailIndexToUsers.php
```

**Example migration:**
```php
class CreateUsersTable extends Migration {
    public function up(PDO $pdo): void {
        $pdo->exec("CREATE TABLE users (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), email VARCHAR(255) UNIQUE)");
    }

    public function down(PDO $pdo): void {
        $pdo->exec("DROP TABLE users");
    }
}
```

---

## API Versioning

Support multiple API versions to enable backward compatibility and gradual deprecation. Use either URL path prefixes or header-based versioning.

**Path-based versioning example:**
```
GET /api/v1/users       # Version 1 response
GET /api/v2/users       # Version 2 response with additional fields
```

**Header-based versioning:**
```
GET /api/users
X-API-Version: 2
```

**Route configuration:**
```json
{
  "groups": [
    {
      "prefix": "/api/v1",
      "routes": [{ "path": "/users", "controller": "Api\\V1\\UserController" }]
    },
    {
      "prefix": "/api/v2",
      "routes": [{ "path": "/users", "controller": "Api\\V2\\UserController" }]
    }
  ]
}
```

---

## Authentication Middleware

Enhance the existing AuthHttpBeforeMiddleware to support JWT validation, bearer token extraction, and role/permission checking for secure API endpoints.

**Enhanced example:**
```php
class AuthHttpBeforeMiddleware extends Middleware {
    public function handle(array $args): bool {
        $request = getRequest();
        $auth = $request->getHeader('Authorization')[0] ?? '';

        if (!preg_match('/Bearer\s+(.+)/', $auth, $matches)) {
            return response()->setStatus(401);
        }

        try {
            $token = JWT::verify($matches[1], config('jwt.secret'));
            $request->setAttribute('user', $token->data);
            return true;
        } catch (Exception $e) {
            return response()->setStatus(403);
        }
    }
}
```

---

## CORS Middleware

Improve CORS handling with configurable allowed origins, preflight request handling, and custom header support for secure cross-origin requests.

**Enhanced CorsBeforeMiddleware:**
```php
class CorsBeforeMiddleware extends Middleware {
    public function handle(array $args): bool {
        $request = getRequest();
        $response = response();
        $allowed = config('cors.origins', ['*']);
        $origin = $request->getHeader('Origin')[0] ?? '*';

        if (in_array($origin, $allowed) || in_array('*', $allowed)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        if ($request->getMethod() === 'OPTIONS') {
            return response()->setStatus(204);
        }

        return true;
    }
}
```

---

## Rate Limiting Middleware

Add rate-limiting to prevent abuse. Track requests per IP address using cache backends (Redis or APCu) with configurable time windows and thresholds.

**Example RateLimitBeforeMiddleware:**
```php
class RateLimitBeforeMiddleware extends Middleware {
    public function handle(array $args): bool {
        $ip = getRequest()->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = "rate_limit:$ip";
        $limit = config('rate_limit.requests_per_minute', 60);

        $count = cache()->get($key, 0);
        if ($count >= $limit) {
            return response()->setStatus(429)->write('Too Many Requests');
        }

        cache()->set($key, $count + 1, 60);
        return true;
    }
}
```

---

## Environment-Specific Examples

Provide configuration templates for production, staging, and development to guide secure deployments and best practices in each environment.

**Example config-prod.json:**
```json
{
  "app": {
    "debug": false,
    "logging": "error"
  },
  "cache": {
    "default": "redis",
    "redis": { "host": "${env:REDIS_HOST}", "port": 6379 }
  },
  "database": {
    "default": "mysql",
    "mysql": {
      "host": "${env:DB_HOST}",
      "port": 3306,
      "database": "${env:DB_NAME}",
      "user": "${env:DB_USER}",
      "password": "${env:DB_PASSWORD}"
    }
  },
  "jwt": { "secret": "${env:JWT_SECRET}", "ttl": 3600 }
}
```

**Example config-staging.json:** Similar to production but with `debug: true` for easier troubleshooting.

---

## Data Seeding

Implement a seeding system for populating test data and initial database state during development and testing.

**Console commands:**
```bash
php console seed:run
php console seed:run UserSeeder
php console seed:fresh
```

**Suggested structure:**
```
src/app/Seeds/
  ├── Seeder.php         # Base seeder class
  ├── UserSeeder.php     # Example seeder
  └── PostSeeder.php
```

**Example seeder:**
```php
class UserSeeder extends Seeder {
    public function run(): void {
        $pdo = app()->getDatabase();
        for ($i = 1; $i <= 10; $i++) {
            $pdo->exec("INSERT INTO users (name, email) VALUES ('User $i', 'user$i@example.com')");
        }
    }
}
```

---

## Docker Compose

Provide a docker-compose.yml for local development that includes app, database, and cache services to eliminate "works on my machine" issues.

**Example docker-compose.yml:**
```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/app
    depends_on:
      - mysql
      - redis

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: spin_app
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

volumes:
  mysql_data:
```

---

## CI/CD Pipeline

Set up GitHub Actions for automated testing, building, and deployment to catch issues early and ensure code quality on every push.

**Example .github/workflows/test.yml:**
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: [8.1, 8.2, 8.3]

    steps:
      - uses: actions/checkout@v3
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
      - run: composer install
      - run: ./vendor/bin/phpunit
      - run: ./vendor/bin/phpstan analyse src/
```

---

## Command-Line Tools

Create a Makefile with common development tasks to simplify onboarding and standardize workflows across the team.

**Example Makefile:**
```makefile
.PHONY: install test serve clean

install:
	composer install

test:
	./vendor/bin/phpunit

serve:
	php -S 127.0.0.1:8000 -t public/

clean:
	rm -rf coverage/ vendor/ .phpunit.cache

lint:
	./vendor/bin/phpstan analyse src/
```

---

## Validation Library

Add form and input validation utilities to reduce boilerplate and improve error handling in controllers.

**Example validator usage:**
```php
$validator = new Validator([
    'email' => 'required|email',
    'age' => 'required|integer|min:18',
    'password' => 'required|minLength:8'
]);

if (!$validator->validate($data)) {
    return responseJson(['errors' => $validator->getErrors()], 422);
}
```

**Suggested structure:**
```
src/app/Validation/
  ├── Validator.php
  ├── Rules/
  │   ├── Rule.php
  │   ├── Required.php
  │   ├── Email.php
  │   └── MinLength.php
```

---

## Advanced Testing

Expand test coverage with database mocking, fixtures, integration tests, and end-to-end testing to ensure reliability at multiple levels.

**Testing patterns:**
- **Unit tests:** Test individual classes in isolation with mocked dependencies
- **Integration tests:** Test modules together with real or in-memory databases
- **End-to-end tests:** Test full request-response cycles via HTTP
- **Fixtures:** Reusable test data in JSON/YAML for consistent setup

**Example integration test:**
```php
class UserRepositoryTest extends TestCase {
    protected function setUp(): void {
        $this->db = new InMemoryDatabase();
    }

    public function testFindUser(): void {
        $repo = new UserRepository($this->db);
        $user = $repo->find(1);
        $this->assertNotNull($user);
    }
}
```

---

## Documentation

Expand documentation with API specifications, architecture records, troubleshooting guides, and performance tuning recommendations.

**Suggested additions:**
- **API Documentation:** OpenAPI/Swagger spec for auto-generated docs and client generation
- **Architecture Decision Records (ADRs):** docs/adr/ with rationale for design choices
- **Troubleshooting Guide:** Common issues, solutions, and debug tips
- **Performance Tuning:** Caching strategies, query optimization, load testing recommendations

**Example ADR structure:**
```
docs/adr/
  ├── 0001-use-json-routing.md
  ├── 0002-psr-compliance.md
```

---

## Next Steps

These improvements are suggestions to enhance the skeleton over time. We recommend:

1. **Prioritize:** Discuss with your team which improvements align with your project goals
2. **Create Issues:** Open GitHub issues for enhancements you'd like to implement
3. **Iterate:** Start with one or two improvements, refine them, and add more as needed
4. **Share:** Document your implementations and update this file with lessons learned

The SPIN Framework is flexible enough to support all these patterns. Choose what works best for your application!
