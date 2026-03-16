<?php declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Middlewares\RequestIdBeforeMiddleware;
use App\Middlewares\RequestIdAfterMiddleware;
use App\Middlewares\ResponseTimeAfterMiddleware;
use App\Middlewares\SessionBeforeMiddleware;

class MiddlewareTest extends TestCase
{
  public function setUp(): void
  {
    global $app;

    // Reset the response to a clean state before each test
    $app->setResponse(
      $app->getResponse()->withStatus(200)
    );
  }

  // -------------------------------------------------------------------------
  // RequestIdBeforeMiddleware
  // -------------------------------------------------------------------------

  public function testRequestIdBefore_setsContainerEntry(): void
  {
    $mw = new RequestIdBeforeMiddleware();
    $mw->initialize([]);
    $result = $mw->handle([]);

    $this->assertTrue($result);
    $requestId = container('requestId');
    $this->assertIsString($requestId);
    $this->assertNotEmpty($requestId);
  }

  // -------------------------------------------------------------------------
  // RequestIdAfterMiddleware
  // -------------------------------------------------------------------------

  public function testRequestIdAfter_addsResponseHeader(): void
  {
    // Set a requestId via the Before middleware first
    $before = new RequestIdBeforeMiddleware();
    $before->initialize([]);
    $before->handle([]);

    $expectedId = container('requestId');
    $this->assertNotEmpty($expectedId);

    $after = new RequestIdAfterMiddleware();
    $after->initialize([]);   // captures requestId from container
    $result = $after->handle([]);

    $this->assertTrue($result);
    $this->assertSame($expectedId, getResponse()->getHeaderLine('X-Request-Id'));
  }

  public function testRequestIdAfter_headerMatchesContainerValue(): void
  {
    // Run Before to ensure container has a value, then run After and verify
    $before = new RequestIdBeforeMiddleware();
    $before->initialize([]);
    $before->handle([]);

    $after = new RequestIdAfterMiddleware();
    $after->initialize([]);
    $after->handle([]);

    $this->assertSame(container('requestId'), getResponse()->getHeaderLine('X-Request-Id'));
  }

  // -------------------------------------------------------------------------
  // ResponseTimeAfterMiddleware
  // -------------------------------------------------------------------------

  public function testResponseTimeAfter_addsResponseTimeHeader(): void
  {
    // Simulate a request start time
    $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true) - 0.05;

    $mw = new ResponseTimeAfterMiddleware();
    $mw->initialize([]);
    $result = $mw->handle([]);

    $this->assertTrue($result);

    // Environment is UNITTEST (not 'prod'), so header must be present
    $header = getResponse()->getHeaderLine('X-Response-Time');
    $this->assertNotEmpty($header);
    $this->assertMatchesRegularExpression('/^\d+\.\d+$/', $header);
  }

  public function testResponseTimeAfter_addsResponseHostHeader(): void
  {
    $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true) - 0.01;

    $mw = new ResponseTimeAfterMiddleware();
    $mw->initialize([]);
    $mw->handle([]);

    $this->assertNotEmpty(getResponse()->getHeaderLine('X-Response-Host'));
  }

  // -------------------------------------------------------------------------
  // SessionBeforeMiddleware
  // -------------------------------------------------------------------------

  public function testSessionBefore_setsContainerSession(): void
  {
    $mw = new SessionBeforeMiddleware();
    $mw->initialize([]);
    $result = $mw->handle([]);

    $this->assertTrue($result);

    $session = container('session');
    $this->assertIsArray($session);
  }

  public function testSessionBefore_sessionContainsCookieKey(): void
  {
    $mw = new SessionBeforeMiddleware();
    $mw->initialize([]);
    $mw->handle([]);

    $session = container('session');
    $this->assertArrayHasKey('cookie', $session);
  }

  public function testSessionBefore_sessionContainsTimeoutKey(): void
  {
    $mw = new SessionBeforeMiddleware();
    $mw->initialize([]);
    $mw->handle([]);

    $session = container('session');
    $this->assertArrayHasKey('timeout', $session);
  }

  public function testSessionBefore_cookieNameMatchesConfig(): void
  {
    $mw = new SessionBeforeMiddleware();
    $mw->initialize([]);
    $mw->handle([]);

    $session = container('session');
    $this->assertSame(config('session.cookie'), $session['cookie']);
  }

  public function testSessionBefore_timeoutMatchesConfig(): void
  {
    $mw = new SessionBeforeMiddleware();
    $mw->initialize([]);
    $mw->handle([]);

    $session = container('session');
    $this->assertSame(config('session.timeout'), $session['timeout']);
  }

  public function tearDown(): void
  {
    unset($_SERVER['REQUEST_TIME_FLOAT']);
  }
}
