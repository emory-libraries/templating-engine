<?php

// Get the request method.
define('METHOD', isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false);

// Initialize a helper method for exiting gracefully.
function done( int $status, $message = null, $code = null ) {
  
  // For command line usage, report errors to the command line.
  if( METHOD === false ) {
  
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
  
  // For all other requests, return the appropriate status code and message.
  else {
    
    // Send the response code.
    http_response_code($code); 
    
    // Set content type header.
    header('Content-Type: application/json');
    
    // Output the message.
    if( isset($message) ) echo json_encode((is_array($message) ? $message : [
      'code' => $code,
      'message' => $message
    ]), JSON_PRETTY_PRINT);
    
    // Exit.
    exit($status);
    
  }
  
}

// Disallow all other request types other than post.
if( METHOD !== 'POST' and METHOD !== false ) done(1, 'Method not allowed.', 405);

// Enable indexing via a post request.
if( METHOD === 'POST' ) {
  
  // Capture post arguments.
  define('OPTIONS', [
    'site' => defined(SITE) ? SITE : $_POST['site'],
    'environment' => defined(ENVIRONMENT) ? ENVIRONMENT : $_POST['environment'],
    'callback' => isset($_POST['callback']) ? $_POST['callback'] : false,
    'development' => isset($_POST['development']) ? filter_var($_POST['development'], FILTER_VALIDATE_BOOLEAN) : false,
    'username' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null,
    'password' => isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null,
  ]);
  
  // Initialize the response output.
  $output = [];
  
}

// Otherwise, enable indexing via command line.
else {

  // Parse command line arguments.
  $options = getopt('s:e:u:p:c::d', [
    'site:',
    'environment:',
    'username:',
    'password:',
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
  
  // Ensure that only strings were captured.
  if( is_array($options['site']) ) $options['site'] = $options['site'][0];
  if( is_array($options['environment']) ) $options['environment'] = $options['environment'][0];
  if( is_array($options['callback']) ) $options['callback'] = $options['callback'][0];
  if( is_array($options['username']) ) $options['username'] = $options['username'][0];
  if( is_array($options['password']) ) $options['password'] = $options['password'][0];
  
  // Save the options.
  define('OPTIONS', $options);
  
}

// Fail immediately if missing any arguments.
if( !isset(OPTIONS['site']) ) done(1, 'Missing site argument.', 400);
if( !isset(OPTIONS['environment']) ) done(1, 'Missing environment argument.', 400);

// Set environment directories.
$environment = [
  'development' => 'dev',
  'qa'          => 'qa',
  'staging'     => 'staging',
  'production'  => 'prod'
];

// Validate the given environment option.
if( !in_array(OPTIONS['environment'], array_keys($environment)) ) done(1, 'Invalid environment.');

// Set environment flag.
if( !defined('ENVIRONMENT') ) define('ENVIRONMENT', OPTIONS['environment']);

// Set development mode flag.
define('DEVELOPMENT', OPTIONS['development']);

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
if( !defined('DOCUMENT_ROOT') ) define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
if( !defined('SERVER_ROOT') ) define('SERVER_ROOT', dirname(dirname(dirname(__DIR__)))); 
if( !defined('SERVER_PATH') ) define('SERVER_PATH', str_replace(DOCUMENT_ROOT.'/', '', SERVER_ROOT));
if( !defined('DATA_ROOT') ) define('DATA_ROOT', SERVER_ROOT.'/data/'.$environment[ENVIRONMENT]);
if( !defined('PATTERNS_ROOT') ) define('PATTERNS_ROOT', SERVER_ROOT.'/patterns/'.$environment[ENVIRONMENT]);
if( !defined('ENGINE_ROOT') ) define('ENGINE_ROOT', SERVER_ROOT.'/engine/'.$environment[ENVIRONMENT]);
if( !defined('CACHE_ROOT') ) define('CACHE_ROOT', SERVER_ROOT.'/engine/'.$environment[ENVIRONMENT].'/php/cache');

// Get a list of known sites.
define('SITES', array_values(array_filter(scandir(DATA_ROOT), function($path) {
  
  // Get the absolute path.
  $path = DATA_ROOT."/$path";
  
  // Filter out any files and environment-level data folders.
  return (is_dir($path) and !in_array(basename($path), ['.', '..', '_global', '_meta', '_shared']));
  
})));

// Validate the given site option.
if( !in_array(OPTIONS['site'], SITES) ) done(1, 'Invalid site.', 400);

// Derive the site's subdomain from the environment.
$subdomain = str_replace('prod', '', $environment[ENVIRONMENT]);

// Set the site and domain globals.
if( !defined('SITE') ) define('SITE', OPTIONS['site']);
if( !defined('DOMAIN') ) define('DOMAIN', ($subdomain !== '' ? "$subdomain." : '').SITE);

// Set site-specific globals.
if( !defined('SITE_DATA') ) define('SITE_DATA', DATA_ROOT.'/'.SITE);
if( !defined('SITE_ROOT') ) define('SITE_ROOT', SERVER_ROOT.'/'.DOMAIN);

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
if( OPTIONS['username'] !== getenv('INDEX_USERNAME') ) done(1, 'Invalid username or password.', 401);
if( OPTIONS['password'] !== getenv('INDEX_PASSWORD') ) done(1, 'Invalid username or password.', 401);

// Start indexing.
new Index();

// If a post request was used, then return a JSON response.
if( METHOD === 'POST' ) {
  
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