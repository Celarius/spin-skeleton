<?php declare(strict_types=1);

namespace App\Controllers;

use \GuzzleHttp\Psr7\Response;
use \App\Controllers\AbstractPlatesController;

class DefaultController extends AbstractPlatesController
{
  /**
   * Handle GET request
   *
   * @param  array<mixed> $args                   Path variable arguments as name=value pairs
   *
   * @return Response                             Response to caller
   */
  public function handleGET(array $args)
  {
    $data[] = ['title'=>'PageTitle','user'=>'Admin'];

    $html = $this->engine->render('index', $data); // loads "/Views/Templates/TheFileName.html"

    # Send the generated page
    return response($html,200);
  }

}
