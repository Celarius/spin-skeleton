<?php declare(strict_types=1);

namespace App\Middlewares;

use \Spin\Core\Middleware;

class ClientInfoBeforeMiddleware extends Middleware
{
  /**
   * Collect client browser/device information and store it in the container.
   *
   * TODO: Implement by parsing request headers (User-Agent, Accept-Language,
   * etc.) into a structured array or object and storing it via container().
   *
   * @param  array  $args   URI parameters as key=value array
   *
   * @return bool           True=OK, False=Failed to handle it
   */
  public function handle(array $args): bool
  {
    // TODO: implement client info collection
    return true;
  }

}
