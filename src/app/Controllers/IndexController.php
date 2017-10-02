<?php declare(strict_types=1);

namespace App\Controllers;

use \App\Controllers\AbstractController;

class IndexController extends AbstractController
{
  /**
   * Handle GET request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   */
  public function handleGET(array $args)
  {
    # Model to send to view
    $model = ['title'=>'PageTitle', 'user'=>'Kim'];

    # Render view
    $html = $this->engine->render('pages::index', $model);

    # Send the generated html
    return response($html);
  }

}
