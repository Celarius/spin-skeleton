<?php declare(strict_types=1);

namespace App\Controllers\Api;

use \App\Controllers\Api\AbstractRESTController;

class InfoController extends AbstractRESTController
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
    $data['code'] = app()->getAppCode();
    $data['name'] = app()->getAppName();
    $data['version'] = app()->getAppVersion();

    return responseJson($data,200);
  }

}