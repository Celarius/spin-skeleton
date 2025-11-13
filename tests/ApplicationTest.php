<?php declare(strict_types=1);

namespace Spin;

use \PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
  /** @var  Application          SPIN Application */
  protected $app;


  /**
   * Setup test
   */
  public function setUp(): void
  {
    global $app;

    $this->app = $app;
  }


  /**
   * Test Application object existance
   */
  public function testAppCreate()
  {
    $this->assertNotNull($this->app,'Application not initialized');
  }
}