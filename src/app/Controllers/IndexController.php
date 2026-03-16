<?php declare(strict_types=1);

namespace App\Controllers;

use \Psr\Http\Message\ResponseInterface;
use \App\Controllers\AbstractPlatesController;

class IndexController extends AbstractPlatesController
{
  /**
   * Handle GET request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   */
  public function handleGET(array $args): ResponseInterface
  {
    # Model to send to view
    $model = ['title'=>'PageTitle', 'user'=>'Friend'];

    # Render view
    $html = $this->engine->render('pages::index', $model);

    # Send the generated html
    return response($html);
  }

}
