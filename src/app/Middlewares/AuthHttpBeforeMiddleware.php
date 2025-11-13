<?php declare(strict_types=1);

/**
 * AuthHttpBeforeMiddleware
 *
 * Provides methods for authenticating requests using different
 * methods.
 *
 * Supported Authentication methds are:
 *   function authBasic()
 *   function authApikey()
 *   function authBearer()    (JWT tokens)
 *
 * Building JWT tokens: http://jwtbuilder.jamiekurtz.com/
 *
 * @package  [Application]
 */

namespace App\Middlewares;

use Spin\Core\Middleware;
use Spin\Helpers\JWT;

class AuthHttpBeforeMiddleware extends Middleware
{
  /** @var        string          Secret string */
  protected $secret;

  /**
   * Initialize
   *
   * @param      array    $args   The arguments
   *
   * @return     boolean
   */
  public function initialize(array $args): bool
  {
    # Get applications global secret
    $this->secret = config('application.secret');

    return true;
  }

  /**
   * Let the Middleware do it's job
   *
   * @param      array  $args   URI parameters as key=value array
   *
   * @return     bool   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    # Params
    $authenticated = false;
    $username = '';
    $password = '';

    $type ='token';
    $token = config('integrations.core.token');
    $authenticated = $this->authToken($token);

    # Failed ?
    if (!$authenticated && getResponse()->getStatusCode()<400) {
      response( '', 401, ['WWW-Authenticate'=>$type.' realm="'.(getRequest()->getHeader('Host')[0] ?? '').'"']);
    }

    return $authenticated;
  }

  /**
   * Basic authentication
   *
   * @param      string  $username  [description]
   * @param      string  $password  [description]
   *
   * @return     bool    False for NOT authenticated, True for success
   */
  protected function authBasic(string $username, string $password)
  {
    $authenticated = false;

    #
    # Authenticate the Username & Password
    #
    // Custom authentication code goes here

    return $authenticated;
  }

  /**
   * Apikey authentication
   *
   * @param      string  $apikey
   *
   * @return     bool    False for NOT authenticated, True for success
   */
  protected function authApikey(string $apikey)
  {
    $authenticated = false;

    #
    # Authenticate the Apikey
    #
    // Custom authentication code goes here

    return $authenticated;
  }

  /**
   * Token authentication
   *
   * @param      string  $token
   *
   * @return     bool    False for NOT authenticated, True for success
   */
  protected function authToken(string $token)
  {
    $authenticated = false;

    # Get the Array of tokens from Config
    $tokens = config('tokens') ?? [];

    # An existing token = success
    $authenticated = array_key_exists($token, $tokens);

    return $authenticated;
  }

  /**
   * Bearer authentication (JWT)
   *
   * @param      string  $token
   *
   * @return     bool    False for NOT authenticated, True for success
   */
  protected function authBearer(string $token)
  {
    $authenticated = false;

    try {
      #
      # Authenticate the JWT payload
      #

      # Verify the Token, and decode the payload - will throw exception on failure
      $payload = JWT::decode($token, $this->secret, 'HS256');

      # Check that there is a valid payload, then we are authenticated
      if (!is_null($payload)) {
        # Store the Payload in the Dependency Container for later use in controller
        container('jwt:payload',$payload);

        $authenticated = true;
      }

    } catch (\Exception $e) {
      # Decoding failed, invalid JWT payload
      logger()->critical($e->getMessage(),['rid'=>container('requestId'),'msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);

    }

    return $authenticated;
  }

}
