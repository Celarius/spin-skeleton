<?php declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spin\Application;

class ApplicationTest extends TestCase
{
  /** @var Application */
  protected Application $app;

  public function setUp(): void
  {
    global $app;
    $this->app = $app;
  }

  public function testAppCreate(): void
  {
    $this->assertNotNull($this->app, 'Application not initialized');
    $this->assertInstanceOf(Application::class, $this->app);
  }

  public function testEnvironmentIsUnittest(): void
  {
    $this->assertSame('unittest', $this->app->getEnvironment());
  }

  public function testConfigLoaded(): void
  {
    $this->assertNotNull(config(), 'Config object is null');
    $this->assertIsBool(config('application.global.maintenance'));
    $this->assertSame('UTC', config('application.global.timezone'));
  }

  public function testLoggerAvailable(): void
  {
    $this->assertNotNull(logger(), 'Logger is null');
    $this->assertInstanceOf(LoggerInterface::class, logger());
  }

  public function testContainerAvailable(): void
  {
    $this->assertNotNull(container(), 'Container is null');
  }

  public function testVersionLoaded(): void
  {
    $this->assertNotEmpty($this->app->getAppCode(), 'App code is empty');
    $this->assertNotEmpty($this->app->getAppName(), 'App name is empty');
    $this->assertNotEmpty($this->app->getAppVersion(), 'App version is empty');
    $this->assertSame('spin-skeleton', $this->app->getAppCode());
  }

  public function testAppPathsExist(): void
  {
    $this->assertDirectoryExists($this->app->getAppPath());
    $this->assertDirectoryExists($this->app->getStoragePath());
  }
}
