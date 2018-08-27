<?php declare(strict_types=1);

namespace App\Middlewares;

use \Spin\Core\Middleware;

# Helper
use \App\Helpers\ClientBrowserInfo;

class ClientInfoBeforeMiddleware extends Middleware
{
  /**
   * Let middleware do it's magic
   *
   * @param      array  $args   URI parameters as key=value array
   *
   * @return     bool   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    # Set array to container
    container('clientInfo', new ClientBrowserInfo());

    return true;
  }

}
