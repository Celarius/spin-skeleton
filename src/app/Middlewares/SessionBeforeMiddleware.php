<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class SessionBeforeMiddleware extends Middleware
{
  /**
   * Retreive or Set the SessionId cookie for the request
   *
   * @param  array  $args           URI parameters as key=value array
   *
   * @return bool                   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    # Build session array
    $session = [];
    $session['cookie'] = config('session.cookie');
    $session['timeout'] = config('session.timeout');
    $session['refresh'] = config('session.refresh');
    if (!empty($session['cookie'])) {
      $session['value'] = cookie( $session['cookie'] );
    } else {
      $session['value'] = '';
    }

    # Set array to container
    container('session', $session);

    return true;
  }

}
