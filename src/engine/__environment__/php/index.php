<?php

// Get the request method.
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;

// Initialize a helper method for exiting gracefully.
function done( int $status, $message = null, $code = null ) {
  
  // Use the global method.
  global $method;
  
  // For post requests, return the appropriate status code and message.
  if( $method === 'POST' ) {
    
    // Send the response code.
    http_response_code($code); 
    
    // Set content type header.
    header('Content-Type: application/json');
    
    // Output the message.
    if( isset($message) ) echo json_encode((is_array($message) ? $message : [
      'code' => $code,
      'message' => $message
    ]), JSON_PRETTY_PRINT);
    
  }
  
  // Otherwise, report errors to the command line.
  else {
  
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
  
}

// Enable indexing via a post request.
if( $method === 'POST' ) {
  
  // Capture post arguments.
  $options = [
    'site' => $_POST['site'],
    'environment' => $_POST['environment'],
    'callback' => isset($_POST['callback']) ? $_POST['callback'] : false,
    'development' => isset($_POST['development']) ? filter_var($_POST['development'], FILTER_VALIDATE_BOOLEAN) : false,
    'username' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null,
    'password' => isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null,
  ];
  
  // Initialize the response output.
  $output = [];
  
}

// Otherwise, enable indexing via command line.
else {

  // Parse command line arguments.
  $options = getopt('s:e:u:p:c::d', [
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
  $options['username'] = isset($options['username']) ? $options['username'] : $options['u'];
  $options['password'] = isset($options['password']) ? $options['password'] : $options['p'];
  $options['development'] = (isset($options['development']) or isset($options['d'])) ? true : false;

  // Unset shorthand options.
  unset($options['s']);
  unset($options['e']);
  unset($options['u']);
  unset($options['p']);
  unset($options['d']);
  unset($options['c']);
  
}

// Fail immediately if missing any arguments.
if( !isset($options['site']) ) done(1, 'Missing site argument.', 400);
if( !isset($options['environment']) ) done(1, 'Missing environment argument.', 400);

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

// Defines flags that can be switched on/off to force certain templating engine behaviors.
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

// Aliases major flags as globals for easier access.
define('DEBUGGING', FLAG['debuggingEnabled']);
define('BENCHMARKING', FLAG['benchmarkingEnabled']);

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
if( !in_array($options['site'], SITES) ) done(1, 'Invalid site.', 400);

// Derive the site's subdomain from the environment.
$subdomain = str_replace('prod', '', $environment[ENVIRONMENT]);

// Set the site and domain globals.
define('SITE', $options['site']);
define('DOMAIN', ($subdomain !== '' ? "$subdomain." : '').SITE);

// Set site-specific globals.
define('SITE_DATA', DATA_ROOT.'/'.SITE);
define('SITE_ROOT', SERVER_ROOT.'/'.DOMAIN);

// Enable debugging and error reporting when in development mode.
if( DEBUGGING ) {
  
  // Report all errors.
  error_reporting(E_ALL);
  
  // Display all errors.
  ini_set('display_errors', 1);
  
}

// Initialize the templating engine.
require ENGINE_ROOT."/php/index.init.php";

// Prevent indexing if the given username and password are not acceptable.
if( $options['username'] !== $_ENV['INDEX_USERNAME'] ) done(1, 'Invalid username or password.', 401);
if( $options['password'] !== $_ENV['INDEX_PASSWORD'] ) done(1, 'Invalid username or password.', 401);

// Start indexing.
new Index();

// Fire any callbacks if given.
if( $options['callback'] !== false ) {
  
  // Get the callback path.
  $callback = __DIR__."/callbacks/{$options['callback']}.php";

  // Look for the callback, and execute it if it exists.
  if( file_exists($callback) ) include $callback;
  
}

// If a post request was used, then return a JSON response.
if( $method === 'POST' ) {
  
  // Set the response code and message.
  $output['code'] = 200;
  $output['message'] = 'Indexing completed successfully.';
  
  // Add performance to the response in development mode.
  if( BENCHMARKING ) $output['performance'] = json_decode((Performance\Performance::export())->toJson(), true);
  
  // Done.
  done(1, $output, $output['code']);
 
}

// Otherwise, output the response to the command line.
else {

  // Output all performance results.
  if( BENCHMARKING ) Performance\Performance::results();

  // Exit.
  done(0, 'Indexing completed successfully.', 200);
  
}

?>