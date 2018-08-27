<?php declare(strict_types=1);

namespace App\Controllers\Api;

use \App\Controllers\AbstractRestController;

class StatusController extends AbstractRestController
{
  /**
   * Handle GET request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleGET(array $args)
  {
    $data = [];
    $data['status'] = 'OK';

    return responseJson($data,200);
  }

}
