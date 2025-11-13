<?php declare(strict_types=1);

use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;

if (!function_exists("responseJsonError")) {
  /**
   * Sends back a JSON error response (for REST API errors)
   *
   * `{
   *   "result": "ERROR",
   *   "code": $code,
   *   "title": $title,
   *   "message": $message
   * }`
   *
   * @param      string    $title     The title
   * @param      string    $message   The message
   * @param      int       $httpCode  The http code
   * @param      mixed     $code      The application specific error code
   *
   * @return     Response
   */
  function responseJsonError(string $title,string $message,int $httpCode=400, mixed $code=null)
  {
    $data['result'] = 'ERROR';
    $data['code'] = $code;
    $data['title'] = $title;
    $data['message'] = $message;
    $data['rid'] =\container('requestId');

    return \responseJson($data,$httpCode);
  }
}

if (!function_exists("isWin")) {
  /**
   * Returns boolena true if OS is Windows
   *
   * @return     bool
   */
  function isWin(): string
  {
    return (\strncasecmp(PHP_OS, 'WIN', 3) == 0);
  }
}

if (!function_exists("array_change_key_case_recursive")) {
  /**
   * Change key-case in an array requirsively
   *
   * @param   array     $arr                                    Array to change
   * @param   int|null  $case                                   Optional. `CASE_LOWER` or `CASE_UPPER`. Defaults to `CASE_LOWER`
   *
   * @return  array                                             Changed array
   */
  function array_change_key_case_recursive(array $arr, int|null $case = \CASE_LOWER)
  {
    return \array_map(function($item){
      if (is_array($item))
        $item = \array_change_key_case_recursive($item);
      return $item;
    },\array_change_key_case($arr, $case));
  }
}

if(!function_exists('http_build_url'))
{
  // Define constants
  define('HTTP_URL_REPLACE',          0x0001);    // Replace every part of the first URL when there's one of the second URL
  define('HTTP_URL_JOIN_PATH',        0x0002);    // Join relative paths
  define('HTTP_URL_JOIN_QUERY',       0x0004);    // Join query strings
  define('HTTP_URL_STRIP_USER',       0x0008);    // Strip any user authentication information
  define('HTTP_URL_STRIP_PASS',       0x0010);    // Strip any password authentication information
  define('HTTP_URL_STRIP_PORT',       0x0020);    // Strip explicit port numbers
  define('HTTP_URL_STRIP_PATH',       0x0040);    // Strip complete path
  define('HTTP_URL_STRIP_QUERY',      0x0080);    // Strip query string
  define('HTTP_URL_STRIP_FRAGMENT',   0x0100);    // Strip any fragments (#identifier)

  // Combination constants
  define('HTTP_URL_STRIP_AUTH',       HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS);
  define('HTTP_URL_STRIP_ALL',        HTTP_URL_STRIP_AUTH | HTTP_URL_STRIP_PORT | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT);

  /**
   * HTTP Build URL
   * Combines arrays in the form of parse_url() into a new string based on specific options
   * @name http_build_url
   * @param string|array $url     The existing URL as a string or result from parse_url
   * @param string|array $parts   Same as $url
   * @param int $flags            URLs are combined based on these
   * @param array &$new_url       If set, filled with array version of new url
   * @return string
   */
  function http_build_url(/*string|array*/ $url, /*string|array*/ $parts = array(), /*int*/ $flags = HTTP_URL_REPLACE, /*array*/ &$new_url = false)
  {
    // If the $url is a string
    if(is_string($url))
    {
        $url = \parse_url($url);
    }

    // If the $parts is a string
    if(is_string($parts))
    {
        $parts  = \parse_url($parts);
    }

    // Scheme and Host are always replaced
    if(isset($parts['scheme'])) $url['scheme']  = $parts['scheme'];
    if(isset($parts['host']))   $url['host']    = $parts['host'];

    // (If applicable) Replace the original URL with it's new parts
    if(\HTTP_URL_REPLACE & $flags)
    {
        // Go through each possible key
        foreach(['user','pass','port','path','query','fragment'] as $key)
        {
            // If it's set in $parts, replace it in $url
            if(isset($parts[$key])) $url[$key]  = $parts[$key];
        }
    }
    else
    {
        // Join the original URL path with the new path
        if(isset($parts['path']) && (HTTP_URL_JOIN_PATH & $flags))
        {
            if(isset($url['path']) && $url['path'] != '')
            {
                // If the URL doesn't start with a slash, we need to merge
                if($url['path'][0] != '/')
                {
                    // If the path ends with a slash, store as is
                    if('/' == $parts['path'][\strlen($parts['path'])-1])
                    {
                        $sBasePath  = $parts['path'];
                    }
                    // Else trim off the file
                    else
                    {
                        // Get just the base directory
                        $sBasePath  = \dirname($parts['path']);
                    }

                    // If it's empty
                    if('' == $sBasePath)    $sBasePath  = '/';

                    // Add the two together
                    $url['path']    = $sBasePath . $url['path'];

                    // Free memory
                    unset($sBasePath);
                }

                if(false !== \strpos($url['path'], './'))
                {
                    // Remove any '../' and their directories
                    while(\preg_match('/\w+\/\.\.\//', $url['path'])){
                        $url['path']    = \preg_replace('/\w+\/\.\.\//', '', $url['path']);
                    }

                    // Remove any './'
                    $url['path'] = \str_replace('./', '', $url['path']);
                }
            }
            else
            {
                $url['path']    = $parts['path'];
            }
        }

        // Join the original query string with the new query string
        if(isset($parts['query']) && (HTTP_URL_JOIN_QUERY & $flags))
        {
            if (isset($url['query']))   $url['query']   .= '&' . $parts['query'];
            else                        $url['query']   = $parts['query'];
        }
    }

    // Strips all the applicable sections of the URL
    if (\HTTP_URL_STRIP_USER & $flags)        unset($url['user']);
    if (\HTTP_URL_STRIP_PASS & $flags)        unset($url['pass']);
    if (\HTTP_URL_STRIP_PORT & $flags)        unset($url['port']);
    if (\HTTP_URL_STRIP_PATH & $flags)        unset($url['path']);
    if (\HTTP_URL_STRIP_QUERY & $flags)       unset($url['query']);
    if (\HTTP_URL_STRIP_FRAGMENT & $flags)    unset($url['fragment']);

    // Store the new associative array in $new_url
    $new_url    = $url;

    // Combine the new elements into a string and return it
    return
          ((isset($url['scheme'])) ? $url['scheme'] . '://' : '')
         .((isset($url['user'])) ? $url['user'] . ((isset($url['pass'])) ? ':' . $url['pass'] : '') .'@' : '')
         .((isset($url['host'])) ? $url['host'] : '')
         .((isset($url['port'])) ? ':' . $url['port'] : '')
         .((isset($url['path'])) ? $url['path'] : '')
         .((isset($url['query'])) ? '?' . $url['query'] : '')
         .((isset($url['fragment'])) ? '#' . $url['fragment'] : '')
    ;
  }
}

if (!function_exists("str_contains")) {
  /**
   * Checks if $haystack contains $needle. Case insensitive check
   *
   * @param   string  $haystack     Data to search
   * @param   string  $needle       String to find in $haystack
   *
   * @return  boolean
   */
  function str_contains(string $haystack, string $needle): bool
  {
    if (empty($haystack) || empty($needle)) return false;

    return (\mb_stripos($haystack, $needle) !== false);
  }
}


if (!function_exists("str_pattern_match")) {
  /**
   * Checks if a $pattern is found in a $haystack
   *
   * @param   string  $haystack    The string to search for matches
   * @param   string  $patterns    space-delimited list of patterns to match. example: "+word -one -two"
   *
   * @return  bool
   */
  function str_pattern_match(?string $haystack, string $patterns): bool
  {
    # No matches on empty params
    if (empty($haystack) || empty($patterns)) return false;

    # Initial variables
    $_mustHave = 0;
    $_mustNotHave = 0;

    $_mustHaveCount = 0;
    $_mustNotHaveCount = 0;

    # Pattern preprocessing
    $patterns = \str_replace('  ',' ',\trim($patterns)); // remove double spaces
    $patterns = \explode(' ',$patterns);
    foreach ($patterns as $i => $pattern)
    {
      if (empty(\trim($pattern))) continue; // Skip empty patterns

      # No prefix pattern -> make +
      if ( (\mb_substr($pattern,0,1) != '+') && (\mb_substr($pattern,0,1) != '-') ) {
        $pattern = '+'.$pattern;
        $patterns[$i] = $pattern;
      }

      # + prefixed pattern
      if (\mb_substr($pattern,0,1)=='+') $_mustHave ++;

      # - prefixed pattern
      if (\mb_substr($pattern,0,1)=='-') $_mustNotHave ++;
    }

    # Loop $patterns, count how many we we must have, must ot have
    foreach ($patterns as $pattern)
    {
      # Extract case and needle
      $case = \mb_substr($pattern,0,1);
      $needle = \mb_substr($pattern,1);

      switch ($case) {
        # + pattern
        case '+':
          if (\str_contains($haystack, $needle)) {
            $_mustHaveCount ++;
          }
          break;

        # - pattern
        case '-':
          if (!\str_contains($haystack, $needle)) {
            $_mustNotHaveCount ++;
          }
          break;
      }
    }

    # Did all matches add upp?
    $match = ($_mustHave == $_mustHaveCount) && ($_mustNotHave == $_mustNotHaveCount);

    return $match;
  }
}

if (!function_exists("generateRefId")) {
  /**
   * Generates a reference id
   *
   * @param   string $prefix            Optional prefix to prepend to result
   *
   * @return  string                    13 character reference id. ex. `49p7qs0n3t0ks`
   */
  function generateRefId(string $prefix=''): string
  {
    $refId = (new \DateTIme('', new \DateTImeZone('UTC')))->format('YmdHisu'); // `u` = Microsecond precision

    return $prefix . \baseConvert($refId, 10, 36);
  }
}
