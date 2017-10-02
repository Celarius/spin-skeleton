<?php
/**
 * Spin Application bootstrap file
 *
 * All HTTP requests land here.
 *
 * @package  [Application]
 */

##############################################################################

  # Composer related autoloads
  $file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
  require_once $file;

  # Create Spin Application (giving path to /src folder)
  $app = new \Spin\Application(__DIR__.DIRECTORY_SEPARATOR.'..');

  # Load Users global functions (if it exists)
  if (file_exists($app->getAppPath().DIRECTORY_SEPARATOR.'Globals.php')) {
    require_once $app->getAppPath().DIRECTORY_SEPARATOR.'Globals.php';
  }

##############################################################################

  try {
    # Run the application
    if ( !$app->run() )
    {
      # if Application did NOT produce an output, let us do it instead
      if (response()->getStatusCode()==0) {
        response()->withStatus(404,'');
      }
    }

  } catch (\Exception $e) {
    # Handle Any/All Exceptions
    logger()->critical('Global Error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()] );

    # Some nice messages
    $msg[] = 'All those moments will be lost in time, like tears in rain. Time to die.  - Blade Runner (1982)';
    $msg[] = 'I\'m melting! Melting! Oh, what a world! What a world!Who ever thought a little girl like you could destroy my beautiful wickedness?! Ah, I\'m going! Ahhh!  - The Wizard of Oz (1939)';
    $msg[] = 'Mother of mercy, is this the end of Rico?  - Little Caesar (1931)';
    $msg[] = 'We win, Gracie  - Armageddon (1998)';
    $msg[] = 'I know now why you cry. But it\'s something I can never do  - Terminator 2 (1991)';
    $msg[] = '...heaven, I\'m in heaven ...  - The Green Mile (1999)';

    # Create response
    responseJson(['message'=>$msg[mt_rand(0,count($msg)-1)],'details'=>''] );

  } finally {
    # Send response back to client
    $app->sendResponse();

  }
