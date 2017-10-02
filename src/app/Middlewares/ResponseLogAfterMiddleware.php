<?php declare(strict_types=1);

namespace App\Middlewares;

use \Spin\Core\Middleware;

class ResponseLogAfterMiddleware extends Middleware
{
  /**
   * Let the Middleware do it's job
   *
   * @param  array  $args           URI parameters as key=value array
   *
   * @return bool                   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    // As of PHP 5.4.0, REQUEST_TIME_FLOAT is available in the $_SERVER superglobal array.
    // It contains the timestamp of the start of the request with microsecond precision.

    $time = microtime(true)-($_SERVER["REQUEST_TIME_FLOAT"] ?? 0);

    # Log the request
    logger()->info(
      '"'.getRequest()->getMethod().' '.getRequest()->getUri().'" '.number_format($time,3,'.',''),
      [container('requestId')]
    );

    return true;
  }

}
