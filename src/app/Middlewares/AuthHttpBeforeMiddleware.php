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

use \Spin\Core\Middleware;
use \Spin\Helpers\JWT;

$this->secret
class AuthHttpBeforeMiddleware extends Middleware
{
  protected $secret;

  public function initialize(array $args)
  {
    # Get applications global secret
    $this->secret = config()->get('application.secret');

    return true;
  }

  /**
   * Let the Middleware do it's job
   *
   * @param  array  $args           URI parameters as key=value array
   * @return bool                   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    # Params
    $authenticated = false;
    $username = '';
    $password = '';

    # Get the Authorization header
    $authorization = getRequest()->getHeader('Authorization')[0] ?? '';

    # Decode into $type and $token
    $a = explode(' ',$authorization);
    $type = $a[0] ?? '';
    $token = $a[1] ?? '';

    # "Basic" Authentication ?
    if (strcasecmp($type,'Basic')==0) {
      list($username,$password) = explode(':',base64_decode($token));
      $authenticated = $this->authBasic($username,$password);

    } else
    # "Apikey" Authentication ?
    if (strcasecmp($type,'Apikey')==0) {
      $authenticated = $this->authApiKey($token);

    } else
    # "Bearer" Authentication (JWT) ?
    if (strcasecmp($type,'Bearer')==0) {
      $authenticated = $this->authBearer($token);

    } else {
      # Unknown authentication method
      response()
        ->setStatusCode(403);
    }

    # Failed ?
    if (!$authenticated) {
      response()
        ->setHeader('WWW-Authenticate', $type.' realm="'.(getRequest()->getHeader('Host')[0] ?? '').'"' )
        ->setStatusCode(401);
    }

    return $authenticated;
  }


  /**
   * Basic authentication
   *
   * @param  string $username [description]
   * @param  string $password [description]
   * @return bool False for NOT authenticated, True for success
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
   * @param  string $apikey
   * @return bool False for NOT authenticated, True for success
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
   * Bearer authentication (JWT)
   *
   * @param  string $token
   * @return bool False for NOT authenticated, True for success
   */
  protected function authBearer(string $token)
  {
    $authenticated = false;

    try {
      #
      # Authenticate the JWT payload
      #

      # Verify the Token, and decode the payload - will throw exception on failure
      $payload = JWT::decode($token, $this->secret, ['HS256']);

      # Check that there is a valid payload, then we are authenticated
      if (!is_null($payload)) {
        # Store the Payload in the Dependency Container for later use in controller
        container('jwt:payload',$payload);

        $authenticated = true;
      }

    } catch (\Exception $e) {
      # Decoding failed, invalid JWT payload
      log()->critical($e->getMessage(),['rid'=>container('requestId'),'msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);

    }

    return $authenticated;
  }

}
