<?php

// Set a localhost and development mode flag.
define('LOCALHOST', ($_SERVER['HTTP_HOST'] == 'localhost' or $_SERVER['SERVER_NAME'] == 'localhost'));
define('NGROK', strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false or strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false);
define('DEVELOPMENT', (LOCALHOST or NGROK or ENVIRONMENT == 'development'));

//Toggle debugging and error reporting when in development mode. Set to true if you'd like to force enable it.
$enable_debug = false;

// Enable debugging and error reporting when in development mode.
if ( DEVELOPMENT ) {
    $enable_debug = true;
}

define('DEBUG_ENABLED', $enable_debug);

if( DEBUG_ENABLED ) {
  // Report all errors.
  error_reporting(E_ALL);

  // Display all errors.
  ini_set('display_errors', 1);

}

// Initialize the templating engine.
require ENGINE_ROOT."/php/init.php";

// Start the templating engine.
new Engine();

// Output all performance result.
if( DEBUG_ENABLED ) {
  Performance\Performance::results();
  d(Performance\Performance::export()->get());
};

?>
