<?php

// Set a localhost and development mode flag.
define('LOCALHOST', ($_SERVER['HTTP_HOST'] == 'localhost' or $_SERVER['SERVER_NAME'] == 'localhost'));
define('NGROK', strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false or strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false);
define('DEVELOPMENT', (LOCALHOST or NGROK or ENVIRONMENT == 'development'));

// Enable debugging and error reporting when in development mode.
if( DEVELOPMENT ) {
  
  // Report all errors.
  error_reporting(E_ALL);
  
  // Display all errors.
  ini_set('display_errors', 1);
  
}

// Initialize the templating engine.
require ENGINE_ROOT."/php/engine.init.php";

// Initialize a global cache.
$cache = new Cache(CACHE_ROOT.'/'.DOMAIN.'.php');

// Start the templating engine.
new Engine();

// Output all performance result.
if( DEVELOPMENT ) {
  Performance\Performance::results();
  d(Performance\Performance::export()->get());
};

?>