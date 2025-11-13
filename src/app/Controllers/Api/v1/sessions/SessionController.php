<?php declare(strict_types=1);

/**
 * Session API Controller
 *
 * Sessions are based on session-cookies
 * Handles session fetching, creation and termination
 */

namespace App\Controllers\Api\V1\Sessions;

use \GuzzleHttp\Psr7\Response;
use \App\Controllers\AbstractRestController;
use \App\Classes\Managers\SessionManager;

class SessionController extends AbstractRestController
{
  /**
   * Request body
   * @var array<mixed>
   */
  public array $body = [];

  /**
   * Session Manager
   * @var SessionManager
   */
  private SessionManager $sessionManager;

#############################################################################################

  public function __construct()
  {
    $this->sessionManager = new SessionManager();
  }

  /**
   * Handle GET request
   *
   * @param  array<mixed> $args                   Path variable arguments as name=value pairs
   *
   * @return Response                             Response to caller
   */
  public function handleGET(array $args)
  {
    try {
      $sessionid = \cookieParam(\config('session.cookie'));
      $session = $this->sessionManager->fetchSession($sessionid);

      if ($session) {
        return responseJson($session);
      }

    } catch (\Exception $e) {

      \logger()->critical('Internal exception', [
        'error' => $e->getMessage(),
        'rid'   => \container('requestId'),
        'trace' => $e->getTraceAsString()
      ]);

      return responseJson(['error' => $e->getMessage()], 500);
    }

    return response('',404);
  }


  /**
   * Verify POST request & paramters
   *
   * @param  array<mixed> $args                   Path variable arguments as name=value pairs
   *
   * @return  Response|null                       Response on error, null if OK
   */
  public function verifyPOST(array $args): ?Response
  {
    # Validate "Content-type"
    if (!\preg_match('/application\/json/i',(\getRequest()->getHeader('Content-Type')[0] ?? ''))) {
      return \responseJsonError('Bad request','Content-Type must be "application/json"',400);
    }

    # Decode payload
    $this->body = (\json_decode(\getRequest()->getBody()->getContents(),true) ?? []);
    if (empty($this->body)) {
      return \responseJsonError('Bad request','Invalid post body', 400);
    }

    # If username&password are missing return error
    if (empty($this->body['username']) || empty($this->body['password'])) {
      return \responseJsonError('Bad request','Missing {username}/{password} parameters',400);
    }

    return null;
  }

  /**
   * Create a new session (login)
   *
   * @param  array<mixed> $args                   Path variable arguments as name=value pairs
   *
   * @return  Response|null                       Response on error, null if OK
   */
  public function handlePOST(array $args)
  {
    try {
      $r = $this->verifyPOST($args);
      if ($r) return $r;

      $username = trim((string)($this->body['username'] ?? ''));
      $password = trim((string)($this->body['password'] ?? ''));

      # Authenticate credentials
      $account = $this->sessionManager->authenticate($username, $password);

      if ($account) {
        // Correct credentials, create a session
        $session = $this->sessionManager->createSession([
          'account_uuid' => $account['uuid']
        ]);
      }

      if ($session) {
          # Set Session Cookie to return
          \cookie(
            \config('session.cookie'),                                // Name
            $session['sessionid'],                                    // Value
            (new \DateTime($session['expires_dt']))->getTimestamp(),  // Expire time as unix timestamp
            '/',                                                      // Path
            '',                                                       // Domain
            false,                                                    // Secure
            true                                                      // HttpOnly (true => only browser sees this)
          );

          return responseJson($session);
      }

    } catch (\Exception $e) {

      \logger()->critical('Internal exception', [
        'error' => $e->getMessage(),
        'rid'   => \container('requestId'),
        'trace' => $e->getTraceAsString()
      ]);

      return responseJson(['error' => $e->getMessage()], 500);
    }

    return responseJson(['error' => 'Invalid username or password'], 401);
  }

  /**
   * Terminate a session (logout)
   *
   * @param  array<mixed> $args                   Path variable arguments as name=value pairs
   *
   * @return  Response|null                       Response on error, null if OK
   */
  public function handleDELETE(array $args)
  {
    try {
      # Always clear session cookie
      \cookie(
        \config('session.cookie'),
        '',
        0,
        '/',
        '',
        false,
        true
      );

      $sessionid = \cookieParam(\config('session.cookie'));
      $session = $this->sessionManager->fetchSession($sessionid);
      if ($session) {
        $this->sessionManager->deleteSession($session);
      }

    } catch (\Exception $e) {

      \logger()->critical('Internal exception', [
        'error' => $e->getMessage(),
        'rid'   => \container('requestId'),
        'trace' => $e->getTraceAsString()
      ]);

      return responseJson(['error' => $e->getMessage()], 500);
    }

    # Always return 204 No Content even if session was not found
    return response('', 204);
  }

}
