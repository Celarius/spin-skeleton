<?php declare(strict_types=1);

namespace App\Middlewares;

use \App\Middlewares\AbstractMiddleware;

class SessionHttpBeforeMiddleware extends AbstractMiddleware
{
  /**
   * Validate session cookie against an external sessions service.
   *
   * TODO: Implement by injecting your HTTP sessions client, calling it with
   * the session cookie value, and returning false (+ setting a 401 response)
   * when the session is missing or expired.
   *
   * @param  array  $args   URI parameters as key=value array
   *
   * @return bool           True=OK, False=Failed to handle it
   */
  public function handle(array $args): bool
  {
    // TODO: implement external session validation
    return true;
  }

}
