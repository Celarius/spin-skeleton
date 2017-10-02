<?php declare(strict_types=1);

namespace App\Controllers;

use \Spin\Core\Controller;

class AController extends Controller
{
  /**
   * Handle GET request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleGET(array $args)
  {
    $html =
      '<html>'.
      '<body>'.
      '<h3>Welcome to the site - A Controller</h3>'.
      '</body>'.
      '</html>';

    # Send the generated page
    return response($html,200);
  }

}
