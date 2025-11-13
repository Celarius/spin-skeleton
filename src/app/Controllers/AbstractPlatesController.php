<?php declare(strict_types=1);

/**
 * Abstract Plates Controller
 *
 * Initializes The Leauge Plates Template engine,
 * loading the settings from the config file
 */

namespace App\Controllers;

use \League\Plates\Engine;
use \Spin\Core\Controller;

abstract class AbstractPlatesController extends Controller
{
  /** @var Engine       The Leauge Template Engine */
  protected $engine;

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

    # Create new Plates instance, default to "/Views" folder
    $this->engine = new Engine(app()->getAppPath().DIRECTORY_SEPARATOR.'Views');

    # Sets the default file extension (from config)
    $this->engine->setFileExtension(config('templates.extension') ?? 'html');

    # Add other folders (from config)
    $this->engine->addFolder('pages', app()->getAppPath().config('templates.pages'));
    $this->engine->addFolder('errors', app()->getAppPath().config('templates.errors'));

    return ;
  }

}
