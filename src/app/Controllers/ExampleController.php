<?php declare(strict_types=1);

namespace App\Controllers;

use \App\Controllers\AbstractPlatesController;

class ExampleController extends AbstractPlatesController
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
      '<h3>Welcome to the site</h3>'.
      '</body>'.
      '</html>';

    # Send the generated page
    return response($html,200);
  }

  /**
   * Handle POST request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handlePOST(array $args)
  {
    # Get a post param
    $postParam = postParam('q'); // null if not found

    # Generate a Not Implemented response
    return response($this->engine->render('errors::405'), 405);
  }

  /**
   * Handle PUT request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handlePUT(array $args)
  {
    # Get a post param
    $postParam = postParam('q'); // null if not found

    # Generate a Not Implemented response
    return response($this->engine->render('errors::405'), 405);
  }

  /**
   * Handle PATCH request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handlePATCH(array $args)
  {
    # Get a post param
    $postParam = postParam('q'); // null if not found

    # Generate a Not Implemented response
    return response($this->engine->render('errors::405'), 405);
  }

  /**
   * Handle DELETE request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleDELETE(array $args)
  {
    # Generate a Not Implemented response
    return response($this->engine->render('errors::405'), 405);
  }

  /**
   * Handle HEAD request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleHEAD(array $args)
  {
    # Do what the GET does, but return NO body
    return $this->handleGET($args)->withBody('');
  }

  /**
   * Handle OPTIONS request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleOPTIONS(array $args)
  {
    # Generate a Not Implemented response
    return response($this->engine->render('errors::405'), 405);
  }

  /**
   * Handle custom request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleCUSTOM(array $args)
  {
    # Generate a Not Implemented response
    return response($this->engine->render('errors::405'), 405);
  }

}
