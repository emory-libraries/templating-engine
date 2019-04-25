<?php

// Set a localhost and development mode flag.
define('LOCALHOST', ($_SERVER['HTTP_HOST'] == 'localhost' or $_SERVER['SERVER_NAME'] == 'localhost'));
define('DEVELOPMENT', (LOCALHOST or ENVIRONMENT == 'development'));

// Enable debugging and error reporting when in development mode.
if( DEVELOPMENT ) {
  
  // Report all errors.
  error_reporting(E_ALL);
  
  // Display all errors.
  ini_set('display_errors', 1);
  
}

// Otherwise, disable debugging and error reporting.
else Kint::$enabled_mode = false;

// Initialize the templating engine.
require ENGINE_ROOT."/php/init.php";

// Start the templating engine.
new Engine();

?>