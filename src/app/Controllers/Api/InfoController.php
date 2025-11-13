<?php declare(strict_types=1);

namespace App\Controllers\Api;

use \GuzzleHttp\Psr7\Response;
use \App\Controllers\AbstractRestController;

class InfoController extends AbstractRestController
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
    $data['code'] = app()->getAppCode();
    $data['name'] = app()->getAppName();
    $data['version'] = app()->getAppVersion();

    return responseJson($data,200);
  }

}
