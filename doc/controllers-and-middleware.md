# Controllers and Middleware Guide

## Request Lifecycle

Every HTTP request flows through Spin Framework in this order:

```
HTTP Request
    ↓
Global Before Middleware
    ↓
Route Matching
    ↓
Group Before Middleware
    ↓
Controller Handler (GET/POST/etc.)
    ↓
Group After Middleware
    ↓
Global After Middleware
    ↓
HTTP Response
```

Middleware can return `false` to short-circuit and skip remaining middleware and the controller.

## Creating Controllers

Controllers extend `Spin\Core\Controller` and handle HTTP requests for your application.

### Basic Structure

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class ExampleController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        return responseJson(['message' => 'Hello, World!']);
    }
}
```

### HTTP Methods

Override methods for each HTTP verb your controller handles:

```php
public function handleGET(array $args): ResponseInterface
public function handlePOST(array $args): ResponseInterface
public function handlePUT(array $args): ResponseInterface
public function handlePATCH(array $args): ResponseInterface
public function handleDELETE(array $args): ResponseInterface
public function handleHEAD(array $args): ResponseInterface
public function handleOPTIONS(array $args): ResponseInterface
```

The `$args` parameter contains path parameters from the route (e.g., `['id' => '123']`).

### REST API Controller Example

```php
<?php declare(strict_types=1);

namespace App\Controllers\Api;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class PostController extends Controller
{
    /**
     * GET /api/posts
     * List all posts
     */
    public function handleGET(array $args): ResponseInterface
    {
        try {
            $db = app()->getConnection('default');
            $stmt = $db->prepare('SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC LIMIT 50');
            $stmt->execute();
            $posts = $stmt->fetchAll();

            return responseJson([
                'data' => $posts,
                'count' => count($posts)
            ], 200);
        } catch (\Exception $e) {
            logger()->error('Failed to fetch posts', ['error' => $e->getMessage()]);
            return responseJson(['error' => 'Failed to fetch posts'], 500);
        }
    }

    /**
     * GET /api/posts/{id}
     * Get single post
     */
    public function handleGET_Detail(array $args): ResponseInterface
    {
        try {
            $db = app()->getConnection('default');
            $stmt = $db->prepare('SELECT * FROM posts WHERE id = ?');
            $stmt->execute([$args['id']]);
            $post = $stmt->fetchAssoc();

            if (!$post) {
                return responseJson(['error' => 'Post not found'], 404);
            }

            return responseJson(['data' => $post], 200);
        } catch (\Exception $e) {
            logger()->error('Post fetch failed', ['error' => $e->getMessage()]);
            return responseJson(['error' => 'Failed to fetch post'], 500);
        }
    }

    /**
     * POST /api/posts
     * Create new post
     */
    public function handlePOST(array $args): ResponseInterface
    {
        try {
            $request = getRequest();
            $json = json_decode($request->getBody()->getContents(), true);

            if (!$json || !isset($json['title'], $json['content'])) {
                return responseJson(['error' => 'Missing required fields'], 400);
            }

            $db = app()->getConnection('default');
            $stmt = $db->prepare('INSERT INTO posts (title, content) VALUES (?, ?)');
            $stmt->execute([$json['title'], $json['content']]);

            return responseJson([
                'id' => $db->lastInsertId(),
                'message' => 'Post created'
            ], 201);
        } catch (\Exception $e) {
            logger()->error('Post creation failed', ['error' => $e->getMessage()]);
            return responseJson(['error' => 'Failed to create post'], 500);
        }
    }

    /**
     * PUT /api/posts/{id}
     * Update post
     */
    public function handlePUT(array $args): ResponseInterface
    {
        try {
            $request = getRequest();
            $json = json_decode($request->getBody()->getContents(), true);
            $id = $args['id'];

            $db = app()->getConnection('default');
            $stmt = $db->prepare('UPDATE posts SET title = ?, content = ? WHERE id = ?');
            $stmt->execute([$json['title'], $json['content'], $id]);

            if ($stmt->rowCount() === 0) {
                return responseJson(['error' => 'Post not found'], 404);
            }

            return responseJson(['message' => 'Post updated'], 200);
        } catch (\Exception $e) {
            logger()->error('Post update failed', ['error' => $e->getMessage()]);
            return responseJson(['error' => 'Failed to update post'], 500);
        }
    }

    /**
     * DELETE /api/posts/{id}
     * Delete post
     */
    public function handleDELETE(array $args): ResponseInterface
    {
        try {
            $db = app()->getConnection('default');
            $stmt = $db->prepare('DELETE FROM posts WHERE id = ?');
            $stmt->execute([$args['id']]);

            if ($stmt->rowCount() === 0) {
                return responseJson(['error' => 'Post not found'], 404);
            }

            return responseJson(['message' => 'Post deleted'], 204);
        } catch (\Exception $e) {
            logger()->error('Post deletion failed', ['error' => $e->getMessage()]);
            return responseJson(['error' => 'Failed to delete post'], 500);
        }
    }
}
```

### Web Page Controller Example

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class PageController extends Controller
{
    /**
     * GET /
     * Homepage
     */
    public function handleGET(array $args): ResponseInterface
    {
        try {
            $db = app()->getConnection('default');
            $stmt = $db->prepare('SELECT * FROM pages WHERE slug = ?');
            $stmt->execute(['home']);
            $page = $stmt->fetchAssoc();

            // Render using template engine (Plates, Twig, etc.)
            $html = view('pages/home', [
                'page' => $page,
                'title' => $page['title'] ?? 'Home',
                'meta_description' => $page['meta_description'] ?? ''
            ]);

            return response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            logger()->error('Page load failed', ['error' => $e->getMessage()]);
            return response('Error loading page', 500);
        }
    }

    /**
     * GET /blog/{slug}
     * Blog post page
     */
    public function handleGET_BlogPost(array $args): ResponseInterface
    {
        try {
            $db = app()->getConnection('default');
            $stmt = $db->prepare('SELECT * FROM blog_posts WHERE slug = ? AND published = 1');
            $stmt->execute([$args['slug']]);
            $post = $stmt->fetchAssoc();

            if (!$post) {
                return view('errors/404', [], 404);
            }

            $html = view('pages/blog-post', [
                'post' => $post,
                'title' => $post['title'],
                'meta_description' => $post['excerpt']
            ]);

            return response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            logger()->error('Blog post load failed', ['error' => $e->getMessage()]);
            return view('errors/500', [], 500);
        }
    }
}
```

## Global Helper Functions

Use these global helpers in controllers, middleware, and models:

### Request and Response

```php
// Get the HTTP request object
$request = getRequest();
$method = $request->getMethod(); // GET, POST, etc.
$path = $request->getUri()->getPath();
$query = $request->getQueryParams();
$headers = $request->getHeaders();
$body = $request->getBody()->getContents();

// Get the HTTP response object
$response = getResponse();

// Send a JSON response
responseJson(['key' => 'value'], 200);

// Send a plain text response
response('Hello, World!', 200);

// Send a response with custom headers
response('Content', 200, ['Content-Type' => 'text/plain']);
```

### Configuration

```php
// Get entire config section
$appConfig = config('application');

// Get nested value with dot notation
$timezone = config('application.global.timezone');
$dbHost = config('connections.default.host');

// Get with default value
$pageSize = config('pagination.items_per_page', 20);
```

### Environment Variables

```php
// Get environment variable
$environment = env('ENVIRONMENT'); // 'dev', 'prod', etc.
$secret = env('APPLICATION_SECRET');
$debug = env('DEBUG_MODE', false);
```

### Logging

```php
logger()->debug('Debug message', ['context' => 'data']);
logger()->info('Information message');
logger()->notice('Notice message');
logger()->warning('Warning message');
logger()->error('Error message', ['error' => $exception->getMessage()]);
logger()->critical('Critical error');
```

### Caching

```php
// Get cache adapter
$cache = cache('local.apcu');

// Set cache (TTL in seconds)
$cache->set('user:123', $userData, 3600);

// Get from cache
$data = $cache->get('user:123');

// Delete from cache
$cache->delete('user:123');

// Clear all
$cache->clear();
```

### Application

```php
// Get the app instance
$app = app();

// Get app metadata
$app->getAppCode();      // 'spin'
$app->getAppName();      // 'Spin Skeleton'
$app->getAppVersion();   // '0.0.1'

// Get database connection
$db = app()->getConnection('default');

// Get dependency container
$container = app()->getContainer();
$custom = container('my_service');
container('my_service', $value);
```

### Views/Templates

```php
// Render a template (requires template engine)
$html = view('pages/home', ['title' => 'Home']);

// Partial/component
$html = view('components/nav', ['activeMenu' => 'home']);
```

## Registering Controllers in Routes

Routes are defined in `routes-{env}.json`. Use the controller class name and method:

```json
{
  "common": {
    "namespace": "App\\Controllers"
  },
  "groups": [],
  "routes": [
    {
      "method": "GET",
      "path": "/",
      "controller": "IndexController",
      "action": "handleGET"
    },
    {
      "method": "GET",
      "path": "/api/posts",
      "controller": "Api\\PostController",
      "action": "handleGET"
    },
    {
      "method": "GET",
      "path": "/api/posts/{id}",
      "controller": "Api\\PostController",
      "action": "handleGET_Detail"
    },
    {
      "method": "POST",
      "path": "/api/posts",
      "controller": "Api\\PostController",
      "action": "handlePOST"
    }
  ]
}
```

## Path Parameters

Extract path parameters from the `$args` array in your controller:

```php
public function handleGET(array $args): ResponseInterface
{
    $userId = $args['id'];      // From /users/{id}
    $postId = $args['postId'];  // From /posts/{postId}
    $slug = $args['slug'];      // From /blog/{slug}

    // Use in query
    $db = app()->getConnection('default');
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetchAssoc();

    return responseJson(['user' => $user]);
}
```

## Creating Middleware

Middleware extends `Spin\Core\Middleware` and implements two methods:

### Basic Structure

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class ExampleMiddleware extends Middleware
{
    /**
     * Initialize
     * Called once when middleware is registered
     */
    public function initialize(array $args): bool
    {
        // Setup code, read configuration
        // Return false to skip this middleware
        return true;
    }

    /**
     * Handle
     * Called for each request
     * Return false to short-circuit remaining middleware and controller
     */
    public function handle(array $args): bool
    {
        // Per-request logic
        return true;
    }
}
```

### Before Middleware Example (Authentication)

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class AuthBeforeMiddleware extends Middleware
{
    private string $secret;

    public function initialize(array $args): bool
    {
        $this->secret = config('application.secret');
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $authHeader = $request->getHeader('Authorization');

        if (empty($authHeader)) {
            // Fail - return 401
            response('Unauthorized', 401, ['WWW-Authenticate' => 'Bearer']);
            return false;
        }

        try {
            $token = str_replace('Bearer ', '', $authHeader[0]);
            $payload = JWT::decode($token, $this->secret, 'HS256');

            // Store in container for use in controller
            container('user', $payload);

            logger()->info('User authenticated', ['user_id' => $payload->sub]);
            return true;

        } catch (\Exception $e) {
            logger()->warning('Auth failed', ['error' => $e->getMessage()]);
            response('Unauthorized', 401);
            return false;
        }
    }
}
```

### After Middleware Example (Logging)

```php
<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class LogResponseAfterMiddleware extends Middleware
{
    public function initialize(array $args): bool
    {
        return true;
    }

    public function handle(array $args): bool
    {
        $request = getRequest();
        $response = getResponse();
        $user = container('user') ?? 'anonymous';

        logger()->info('Request completed', [
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'status' => $response->getStatusCode(),
            'user' => $user,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // Always return true in after middleware
        return true;
    }
}
```

## Registering Middleware

Define middleware in `config-{env}.json`:

```json
{
  "middlewares": {
    "global": {
      "before": [
        { "class": "App\\Middlewares\\RequestIdBeforeMiddleware" },
        { "class": "App\\Middlewares\\CorsBeforeMiddleware" }
      ],
      "after": [
        { "class": "App\\Middlewares\\ResponseLogAfterMiddleware" }
      ]
    },
    "groups": {
      "api": {
        "before": [
          { "class": "App\\Middlewares\\AuthBeforeMiddleware" }
        ],
        "after": []
      }
    }
  }
}
```

Register routes with group:

```json
{
  "groups": [
    {
      "prefix": "/api",
      "name": "api",
      "routes": [
        {
          "method": "GET",
          "path": "/posts",
          "controller": "Api\\PostController",
          "action": "handleGET"
        }
      ]
    }
  ]
}
```

## Error Handling

Create error controllers to handle HTTP errors:

### 4xx Errors

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class Error4xxController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $statusCode = $args['status_code'] ?? 404;
        $message = match($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            default => 'Client Error'
        };

        return responseJson([
            'error' => $message,
            'code' => $statusCode
        ], $statusCode);
    }
}
```

### 5xx Errors

```php
<?php declare(strict_types=1);

namespace App\Controllers;

use Spin\Core\Controller;
use Psr\Http\Message\ResponseInterface;

class Error5xxController extends Controller
{
    public function handleGET(array $args): ResponseInterface
    {
        $statusCode = $args['status_code'] ?? 500;

        logger()->error('Server error', [
            'status_code' => $statusCode,
            'request_id' => container('requestId')
        ]);

        return responseJson([
            'error' => 'Internal Server Error',
            'code' => 500,
            'request_id' => container('requestId')
        ], 500);
    }
}
```

Register error handlers:

```json
{
  "errorControllers": {
    "4xx": { "class": "App\\Controllers\\Error4xxController" },
    "5xx": { "class": "App\\Controllers\\Error5xxController" }
  }
}
```

## See Also

- [Getting Started Guide](getting-started.md)
- [Configuration Guide](configuration.md)
- [Database Connections Guide](database-connections.md)
- [Testing Guide](testing.md)
