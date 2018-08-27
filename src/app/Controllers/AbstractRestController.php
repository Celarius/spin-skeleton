<?php declare(strict_types=1);

/**
 * Abstract Plates Controller
 *
 * Initializes The Leauge Plates Template engine,
 * loading the settings from the config file
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
   * @param  array $args    Path variable arguments as name=value pairs
   */
  public function initialize(array $args)
  {
    parent::initialize($args);

    $response = responseJson([],404)->withHeader('Content-Type','application/json');
    app()->setResponse($response);

    return ;
  }

}
