<?php declare(strict_types=1);

/**
 * CorsBeforeMiddleware
 *
 * Allows everything for sources sending an Origin header.
 *
 * @package  [Application]
 */

namespace App\Middlewares;

use Spin\Core\Middleware;

class CorsBeforeMiddleware extends Middleware
{
  /** @var        string          [description] */
  protected $origin;

  /** @var        string          [description] */
  protected $method;

  /** @var        array          [description] */
  protected $headers;

  /** @var        bool          [description] */
  protected $allowed;

  /**
   * Initialization
   *
   * This method is called right after the Middleware has been created before
   * any of the handle methods get called
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   True=OK, False=Failed to initialize
   */
  public function initialize(array $args): bool
  {
    # Get the Request CORS related headers
    $this->origin = getRequest()->getHeader('Origin')[0] ?? '';
    $this->method = getRequest()->getHeader('Access-Control-Request-Method')[0] ?? '';
    $this->headers = getRequest()->getHeader('Access-Control-Request-Headers');

    # Set the default allowed condition
    $this->allowed = false;

    return true;
  }

  /**
   * Let the Middleware do it's job
   *
   * @param      array  $args   URI parameters as key=value array
   *
   * @return     bool   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    # Is this a CORS headers enabled request at all?
    if (empty($this->origin) && empty($this->method)) {
      # Nope, no CORS headers, so ok, nothing to do

      return true;
    }

    # Always add the Allow-Origin
    $response = getResponse()
                ->withHeader('Access-Control-Allow-Origin', $this->origin )
                ;

    # On OPTIONS requests, add the rest of the headers
    if (strtoupper(getRequest()->getMethod())==='OPTIONS') {
      $response = getResponse()
                  ->withHeader('Access-Control-Allow-Credentials', 'true' )
                  ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH' )
                  ->withHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, *' )
                  ->withHeader('Access-Control-Max-Age: 86400')
                  ->withHeader('Timing-Allow-Origin', '*' )
                  ;
    }

    # Set the modified response
    app()->setResponse($response);

    return $this->allowed;
  }

}
