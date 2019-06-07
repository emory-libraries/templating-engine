<?php

// Autoload dependencies.
require __DIR__."/index.autoload.php";

// Use dependencies.
use Index\API;
use Index\CLI;
use Index\Options;

// Get options.
$options = new Options();

// Determine if development mode should be enabled.
define('DEVELOPMENT', $options->development);
  
// Defines flags that can be switched on/off to force certain indexing behaviors.
define('FLAG', [

  
  // Debugging includes error reporting and displaying as well as all other messaging.
  // By default, debugging is only enabled in development mode. Changing this flag
  // with either force enable (true) or disable (false) debugging.
  'debuggingEnabled' => /*DEVELOPMENT*/true,
  
  // Benchmarking is the process of analyzing the execution time of templating engine
  // processes in an attempt to optimize its performance. By default, the built-in
  // benchmarking tool is only enabled development mode. Changing this flag will
  // either force enable (true) or disable (false) the benchmarking tool.
  'benchmarkingEnabled' => /*DEVELOPMENT*/true
  
]);

// Alias flags as globals for easier access.
define('DEBUGGING', FLAG['debuggingEnabled']);
define('BENCHMARKING', FLAG['benchmarkingEnabled']);

// Always report errors if debugging is enabled.
if( DEBUGGING ) {
  
  // Report all errors.
  error_reporting(E_ALL);
  
  // Display all errors.
  ini_set('display_errors', 1);
  
}

// Configure environment directories.
define('ENVDIR', [
  'development' => 'dev',
  'qa'          => 'qa',
  'staging'     => 'staging',
  'production'  => 'prod'
]);

// Set environment constant.
if( !defined('ENVIRONMENT') ) define('ENVIRONMENT', $options->environment);

// Set path contants.
if( !defined('DOCUMENT_ROOT') ) define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
if( !defined('SERVER_ROOT') ) define('SERVER_ROOT', dirname(dirname(dirname(__DIR__)))); 
if( !defined('SERVER_PATH') ) define('SERVER_PATH', str_replace(DOCUMENT_ROOT.'/', '', SERVER_ROOT));
if( !defined('DATA_ROOT') ) define('DATA_ROOT', SERVER_ROOT.'/data/'.ENVDIR[ENVIRONMENT]);
if( !defined('PATTERNS_ROOT') ) define('PATTERNS_ROOT', SERVER_ROOT.'/patterns/'.ENVDIR[ENVIRONMENT]);
if( !defined('ENGINE_ROOT') ) define('ENGINE_ROOT', SERVER_ROOT.'/engine/'.ENVDIR[ENVIRONMENT]);
if( !defined('CACHE_ROOT') ) define('CACHE_ROOT', SERVER_ROOT.'/engine/'.ENVDIR[ENVIRONMENT].'/php/cache');

// Define a list of known sites.
if( !defined('SITES') ) define('SITES', array_values(array_filter(scandir(DATA_ROOT), function($path) {

  // Get the absolute path.
  $path = DATA_ROOT."/$path";

  // Filter out any files and environment-level data folders.
  return (is_dir($path) and !in_array(basename($path), ['.', '..', '_global', '_meta', '_shared']));

})));

// Set site constants.
if( !defined('SUBDOMAIN') ) define('SUBDOMAIN', str_replace('prod', '', ENVDIR[ENVIRONMENT]));
if( !defined('SITE') ) define('SITE', $options->site);
if( !defined('DOMAIN') ) define('DOMAIN', (SUBDOMAIN !== '' ? SUBDOMAIN.'.' : '').SITE);
if( !defined('SITE_DATA') ) define('SITE_DATA', DATA_ROOT.'/'.SITE);
if( !defined('SITE_ROOT') ) define('SITE_ROOT', SERVER_ROOT.'/'.DOMAIN);

// Initialize the templating engine.
require __DIR__."/index.init.php";

// Initialize the API or CLI.
$API = new API($options);

// Get the API or CLI response.
$API->response();

?>