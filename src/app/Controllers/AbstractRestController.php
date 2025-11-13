<?php declare(strict_types=1);

/**
 * Abstract REST Controller
 *
 * Initializes a response with 404 by default
 */

namespace App\Controllers;

use \Spin\Core\Controller;

abstract class AbstractRestController extends Controller
{
  /**
   * Initialization method
   *
   * This method is called right after the controller has been created
   * before any route specific Middleware handlers
   *
   * @param  array<mixed> $args                   Path variable arguments as name=value pairs
   */
  public function initialize(array $args)
  {
    parent::initialize($args);

    $response = \responseJson([],404)->withHeader('Content-Type','application/json');
    \app()->setResponse($response);
  }

}
