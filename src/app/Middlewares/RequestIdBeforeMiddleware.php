<?php declare(strict_types=1);

namespace App\Middlewares;

use \Spin\Core\Middleware;

class RequestIdBeforeMiddleware extends Middleware
{
  /**
   * Add a unique 'requestId' to the container
   *
   * @param  array  $args           URI parameters as key=value array
   *
   * @return bool                   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    # Set requestId
    container('requestId', md5(microtime(true)));

    return true;
  }

}
