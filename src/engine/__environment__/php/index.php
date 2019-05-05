<?php

// Initialize a helper method for exiting gracefully.
function done( int $status, $message = null ) {
  
  // Log messages if given.
  if( isset($message) ) {
  
    // Get the appropriate I/O stream.
    $stream = $status === 0 ? STDIN : STDERR;

    // Write the message.
    fwrite($stream, $message.PHP_EOL);
    
  }
  
  // Then, exit.
  exit($status);
  
}

// Parse command line arguments.
$options = getopt('s:e:k:c::d', [
  'site:',
  'environment:',
  'key:',
  'callback::',
  'development',
]);

// Normalizes the options.
$options['site'] = isset($options['site']) ? $options['site'] : $options['s'];
$options['environment'] = isset($options['environment']) ? $options['environment'] : $options['e'];
$options['callback'] = isset($options['callback']) ? $options['callback'] : isset($options['c']) ? $options['c'] : false;
$options['key'] = isset($options['key']) ? $options['key'] : $options['k'];
$options['development'] = (isset($options['development']) or isset($options['d'])) ? true : false;

// Unset shorthand options.
unset($options['s']);
unset($options['e']);
unset($options['k']);
unset($options['d']);
unset($options['c']);

// Fail immediately if missing any arguments.
if( !isset($options['site']) ) done(1, 'Missing site argument.');
if( !isset($options['environment']) ) done(1, 'Missing environment argument.');

// Set environment directories.
$environment = [
  'development' => 'dev',
  'qa'          => 'qa',
  'staging'     => 'staging',
  'production'  => 'prod'
];

// Validate the given environment option.
if( !in_array($options['environment'], array_keys($environment)) ) done(1, 'Invalid environment.');

// Set environment flag.
define('ENVIRONMENT', $options['environment']);

// Set development mode flag.
define('DEVELOPMENT', $options['development']);

// Set path globals.
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('SERVER_ROOT', dirname(dirname(dirname(__DIR__)))); 
define('SERVER_PATH', str_replace(DOCUMENT_ROOT.'/', '', SERVER_ROOT));
define('DATA_ROOT', SERVER_ROOT.'/data/'.$environment[ENVIRONMENT]);
define('PATTERNS_ROOT', SERVER_ROOT.'/patterns/'.$environment[ENVIRONMENT]);
define('ENGINE_ROOT', SERVER_ROOT.'/engine/'.$environment[ENVIRONMENT]);
define('CACHE_ROOT', SERVER_ROOT.'/engine/'.$environment[ENVIRONMENT].'/php/cache');

// Get a list of known sites.
define('SITES', array_values(array_filter(scandir(DATA_ROOT), function($path) {
  
  // Get the absolute path.
  $path = DATA_ROOT."/$path";
  
  // Filter out any files and environment-level data folders.
  return (is_dir($path) and !in_array(basename($path), ['.', '..', '_global', '_meta', '_shared']));
  
})));

// Validate the given site option.
if( !in_array($options['site'], SITES) ) done(1, 'Invalid site.');

// Derive the site's subdomain from the environment.
$subdomain = str_replace('prod', '', $environment[ENVIRONMENT]);

// Set the site and domain globals.
define('SITE', $options['site']);
define('DOMAIN', ($subdomain !== '' ? "$subdomain." : '').SITE);

// Set site-specific globals.
define('SITE_DATA', DATA_ROOT.'/'.SITE);
define('SITE_ROOT', SERVER_ROOT.'/'.DOMAIN);

// Enable debugging and error reporting when in development mode.
if( DEVELOPMENT ) {
  
  // Report all errors.
  error_reporting(E_ALL);
  
  // Display all errors.
  ini_set('display_errors', 1);
  
}

// Initialize the templating engine.
require ENGINE_ROOT."/php/index.init.php";

// Prevent indexing if the given key is not acceptable.
if( $options['key'] !== $_ENV['INDEX_KEY'] ) done(1, 'Invalid key');

// Start indexing.
new Index();

// Fire any callbacks if given.
if( $options['callback'] !== false ) {
  
  // Get the callback path.
  $callback = __DIR__."/callbacks/{$options['callback']}.php";

  // Look for the callback, and execute it if it exists.
  if( file_exists($callback) ) include $callback;
  
}

// Output all performance results.
if( DEVELOPMENT ) Performance\Performance::results();

// Exit.
done(0);

?>