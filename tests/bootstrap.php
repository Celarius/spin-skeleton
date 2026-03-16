<?php declare(strict_types=1);

/**
 * Test Bootstrapper
 */

date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

################################################################################
# Initialization code here
################################################################################

# Set environment BEFORE constructing the app so config-unittest.json is loaded
putenv('ENVIRONMENT=UNITTEST');

# Create application (passing /src dir as param)
$app = new \Spin\Application( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' );

# Load Users global functions (if it exists)
if (file_exists($app->getAppPath() . DIRECTORY_SEPARATOR . 'Globals.php')) {
  require_once $app->getAppPath() . DIRECTORY_SEPARATOR . 'Globals.php';
}

# Environment was already set via putenv() above; confirm it here
$app->setEnvironment('UNITTEST');
