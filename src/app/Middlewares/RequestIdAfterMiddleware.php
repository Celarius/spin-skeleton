<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class RequestIdAfterMiddleware extends Middleware
{
  /** @var string */
  protected string $requestId;

  /**
   * Initialization method
   *
   * This method is called right after the Middleware has been created
   * before any of the handle methods get called
   *
   * @param  array $args    Path variable arguments as name=value pairs
   *
   * @return bool                   True=OK, False=Failed to initialize
   */
  public function initialize(array $args): bool
  {
    # Get the requestId
    $this->requestId = container('requestId');

    return true;
  }

  /**
   * Let the Middleware do it's job
   *
   * @param  array  $args           URI parameters as key=value array
   * @return bool                   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    # Add "X-Request-Id" Header
    $response = getResponse()
                ->withHeader('X-Request-Id', (string) $this->requestId);

    # Set the response
    app()->setResponse($response);

    return true;
  }

}
