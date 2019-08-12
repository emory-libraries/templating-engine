<?php

// Increase memory limit for large pages.
ini_set('memory_limit', '300M');

// Autoload dependencies.
require __DIR__.'/engine.autoload.php';

// Set a localhost and development mode flag.
define('LOCALHOST', ($_SERVER['HTTP_HOST'] == 'localhost' or $_SERVER['SERVER_NAME'] == 'localhost'));
define('NGROK', strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false or strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false);
define('DEVELOPMENT', (LOCALHOST or NGROK or ENVIRONMENT == 'development'));

// Defines flags that can be switched on/off to force certain templating engine behaviors.
define('FLAG', [

  // Caching is used by the API to prevent frequent requests on the same resources.
  // By default, caching is only disabled in development mode. Changing this flag
  // will either force enable (true) or disable (false) caching.
  'cachingEnabled' => /*!DEVELOPMENT*/true,

  // Debugging includes error reporting and displaying as well as all other messaging.
  // By default, debugging is only enabled in development mode. Changing this flag
  // with either force enable (true) or disable (false) debugging.
  'debuggingEnabled' => DEVELOPMENT,

  // Benchmarking is the process of analyzing the execution time of templating engine
  // processes in an attempt to optimize its performance. By default, the built-in
  // benchmarking tool is only enabled development mode. Changing this flag will
  // either force enable (true) or disable (false) the benchmarking tool.
  'benchmarkingEnabled' => DEVELOPMENT

]);

// Aliases major flags as globals for easier access.
define('CACHING', FLAG['cachingEnabled']);
define('DEBUGGING', FLAG['debuggingEnabled']);
define('BENCHMARKING', FLAG['benchmarkingEnabled']);

// Enable debugging and error reporting when in development mode.
if( DEBUGGING ) {

  // Report all errors.
  error_reporting(E_ALL);

  // Display all errors.
  ini_set('display_errors', true);

  // Don't save them to the error log.
  ini_set('log_errors', false);

}

// Otherwise, only log errors to the error log.
else {

  // Report all errors.
  error_reporting(E_ALL);

  // Don't displayed them.
  ini_set('display_errors', false);

  // Save them to the error log instead.
  ini_set('log_errors', true);

}

// Initialize the templating engine.
require __DIR__."/engine.init.php";

// Start the templating engine.
new Engine();

// Output all performance result.
if( BENCHMARKING and !ASSET ) {

  // Output the results of the benchmarking tests.
  Performance\Performance::results();

  // Also, output the results to the debugger.
  if( DEBUGGING ) d(Performance\Performance::export()->get());

};

?>
