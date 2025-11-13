<?php declare(strict_types=1);

namespace App\Controllers\Api;

use \GuzzleHttp\Psr7\Response;
use \App\Controllers\AbstractRestController;

class StatusController extends AbstractRestController
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
    $data = [];
    $data['status'] = 'OK';

    return responseJson($data,200);
  }

}
