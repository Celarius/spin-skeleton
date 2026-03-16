<?php declare(strict_types=1);

namespace App\Controllers;

use \Spin\Core\Controller;
use \Psr\Http\Message\ResponseInterface;

class Error4xxController extends Controller
{
  /**
   * Handle 4xx error responses
   *
   * @param  array<mixed> $args   Path variable arguments as name=value pairs
   *
   * @return ResponseInterface
   */
  public function handleGET(array $args): ResponseInterface
  {
    $code = (int) (\app()->getResponse()->getStatusCode() ?: 400);

    return responseJson([
      'error'   => 'Client Error',
      'status'  => $code,
    ], $code);
  }
}
