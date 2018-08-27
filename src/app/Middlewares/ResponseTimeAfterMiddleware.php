<?php declare(strict_types=1);

namespace App\Middlewares;

use Spin\Core\Middleware;

class ResponseTimeAfterMiddleware extends Middleware
{
  /**
   * Let the Middleware do it's job
   *
   * @param  array  $args           URI parameters as key=value array
   * @return bool                   True=OK, False=Failed to handle it
   */
  function handle(array $args): bool
  {
    $responseTime = microtime(true) - ($_SERVER["REQUEST_TIME_FLOAT"] ?? 0);
    $responseSize = mb_strlen( (string)getResponse()->getBody() );

    # For Non-PROD environments, Add headers
    if ( app('environment') !== 'prod') {
      $response = getResponse()
                  ->withHeader('X-Response-Time', number_format($responseTime,3,'.','') )
                  // ->withHeader('X-Response-Size', number_format($responseSize,0,'.','') )
                  ->withHeader('X-Response-Host', gethostname() . '/' . gethostbyname(gethostname()) )
                  ;

      # Set the response
      app()->setResponse($response);
    }

    // # Add some node specific stats information
    // if (cache()) {
    //   $totalRequests = cache()->get(app('code').':system:requests.total',0) + 1;
    //   cache()->set(app('code').':system:requests.total',$totalRequests,0);

    //   $totalResponseTime = (int) cache()->get(app('code').':system:response.time.total',0) + $responseTime;
    //   cache()->set(app('code').':system:response.time.total',$totalResponseTime,0);

    //   $totalResponseSize = (int) cache()->get(app('code').':system:response.size.total',0) + $responseSize;
    //   cache()->set(app('code').':system:response.size.total',$totalResponseSize,0);
    // }

    return true;
  }

}
