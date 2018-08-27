<?php declare(strict_types=1);

namespace App\Middlewares;

use \Spin\Core\Middleware;

use \App\Clients\ComponentRegistry\Client AS CRClient;
use \App\Clients\Sessions\Client AS SessionsClient;

class SessionHttpBeforeMiddleware extends Middleware
{
  /**
   * Retreive the SessionId cookie for the request
   *
   * @param  array  $args           URI parameters as key=value array
   *
   * @return bool                   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    $session_valid = false;

    # Get the Session Cookie's value
    $sessionId = cookieParam(config('session.cookie'));

    if (empty($sessionId)) {
      return responseJsonError('Not authenticated','',401);
    }

    $token = config('integrations.core.token');
    $Sessions = new SessionsClient();
    $response = $Sessions->CheckSessions($sessionId,$token);

    ### EXAMPLE OF OPTIMIZED IMPLEMENTATION

    // TODO: Handle sessions without an expires_dt (it may be null for eternal sessions)

    # Check response
    if ($response && $response->getStatusCode()==200) {
      
      # Get the body
      $body = (string)$response->getBody()->getContents();

      # Decode response
      $decode = json_decode($body);

      if (!empty($decode) && !empty($decode[0]->sessionid)) {
        # If sessionid is here we check expired date & time
        $current_dt = (new \DateTime('now',new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
        $expires_dt = (new \DateTime($decode[0]->expires_dt,new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
        
        if($current_dt < $expires_dt) {
            $session_valid = true;
        }
      }
    }
    
    if (!$session_valid) {
      # Set 401 Not Authenticated
      response('',401);
    }

    return $session_valid;

    ### END OF :: EXAMPLE OF OPTIMIZED IMPLEMENTATION




    // --
    if($response) {

      if($response->getStatusCode()==200) {

        # Get the response from core
        $body = (string)$response->getBody()->getContents();

      # Decode response
      $decode = json_decode($body);
      if(!empty($decode)) {

          # If sessionid is der we check whether it expired or not
          if(!empty($decode[0]->sessionid)) {   
            $current_dt = (new \DateTime('now',new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
            $expires_dt = (new \DateTime($decode[0]->expires_dt,new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
          
            if($current_dt < $expires_dt) {
              $session_valid = true;
            } else {
              response('',401);
            }
          } 

        } else {
          response('',404);
        }

      } else {
        response('',404);
      }

    } else {
      response('',404);
    }

    return $session_valid;
  }
  
}
