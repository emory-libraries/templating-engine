<?php

// Set the namespace.
namespace Index;

// Use dependencies.
use Performance\Performance;
use Performance\Config;
use Index;

/**
 * API
 *
 * This enables a public interface to execute and 
 * otherwise utilize the index via HTTP request or
 * command line.
 */
class API {
  
  // The the API mode, either `API` or `CLI`.
  protected static $mode = 'API';
  
  // The incoming request method.
  protected static $method = 'GET';
  
  // The incoming request endpoint.
  protected static $endpoint = '/';
  
  // The options passed along with the incoming request.
  protected static $options;
  
  // Register known API endpoints and their respective methods.
  protected static $endpoints = [
    'GET' => [
      '/status'   => 'getStatus'
    ],
    'POST' => [
      '/index'    => 'createIndex'
    ]
  ];
  
  // A list of known response codes and their respective statuses.
  protected static $statuses = [
    100 => 'Continue',
    101 => 'Switching Protocols',
    102 => 'Processing', // WebDAV; RFC 2518
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information', // since HTTP/1.1
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    207 => 'Multi-Status', // WebDAV; RFC 4918
    208 => 'Already Reported', // WebDAV; RFC 5842
    226 => 'IM Used', // RFC 3229
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other', // since HTTP/1.1
    304 => 'Not Modified',
    305 => 'Use Proxy', // since HTTP/1.1
    306 => 'Switch Proxy',
    307 => 'Temporary Redirect', // since HTTP/1.1
    308 => 'Permanent Redirect', // approved as experimental RFC
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Request Entity Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    418 => 'I\'m a teapot', // RFC 2324
    419 => 'Authentication Timeout', // not in RFC 2616
    420 => 'Enhance Your Calm', // Twitter
    420 => 'Method Failure', // Spring Framework
    422 => 'Unprocessable Entity', // WebDAV; RFC 4918
    423 => 'Locked', // WebDAV; RFC 4918
    424 => 'Failed Dependency', // WebDAV; RFC 4918
    424 => 'Method Failure', // WebDAV)
    425 => 'Unordered Collection', // Internet draft
    426 => 'Upgrade Required', // RFC 2817
    428 => 'Precondition Required', // RFC 6585
    429 => 'Too Many Requests', // RFC 6585
    431 => 'Request Header Fields Too Large', // RFC 6585
    444 => 'No Response', // Nginx
    449 => 'Retry With', // Microsoft
    450 => 'Blocked by Windows Parental Controls', // Microsoft
    451 => 'Redirect', // Microsoft
    451 => 'Unavailable For Legal Reasons', // Internet draft
    494 => 'Request Header Too Large', // Nginx
    495 => 'Cert Error', // Nginx
    496 => 'No Cert', // Nginx
    497 => 'HTTP to HTTPS', // Nginx
    499 => 'Client Closed Request', // Nginx
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    506 => 'Variant Also Negotiates', // RFC 2295
    507 => 'Insufficient Storage', // WebDAV; RFC 4918
    508 => 'Loop Detected', // WebDAV; RFC 5842
    509 => 'Bandwidth Limit Exceeded', // Apache bw/limited extension
    510 => 'Not Extended', // RFC 2774
    511 => 'Network Authentication Required', // RFC 6585
    598 => 'Network read timeout error', // Unknown
    599 => 'Network connect timeout error', // Unknown
  ];
  
  // A list of known exit codes and their respective I/O streams.
  protected static $streams = [
    0 => null,
    1 => null
  ];
  
  // Defines flags that can be used to tailor the API response.
  const INDEXAPI_RESPONSE_RETURN = 1;
  
  //*********** MAGIC METHODS ***********//
  
  // Construct the API.
  function __construct( Options $options ) {
    
    // Initialize the API.
    self::init();
    
    // Initialize performance point.
    if( BENCHMARKING ) {
      
      // Configure live benchmarking.
      Config::setConsoleLive(true);
      
      // Start benchmarking.
      Performance::point('Index API', true);
      
    }
    
    // Determine the API's mode, either `API` or `CLI`.
    self::$mode = isset($_SERVER['REQUEST_METHOD']) ? 'API' : 'CLI';
    
    // Get information about the incoming request.
    self::$options = $options;
    self::$method = $options->method;
    self::$endpoint = $options->endpoint;
    
  }
  
  // Call the appropriate method to process the incoming API request.
  static function __callStatic( $method, $arguments ) {

    // If the method does not exist, then respond with an error.
    if( !method_exists(__CLASS__, $method) ) return static::done(501, strtoupper($method).' method is not currently implemented.');
    
    // Get a reflection of the method trying to be called.
    $reflection = new ReflectionMethod(__CLASS__, $method);
    
    // If the method is not public, then also respond with an error.
    if( !$reflection::isPublic() ) return static::done(501, strtoupper($method).' method is not currently implemented.');
    
    // Otherwise, forward the request to the method.
    return call_user_func_array(__CLASS__."::$method", $arguments);
    
  }
  
  //*********** PUBLIC METHODS ***********//
  
  // Signal that the response is done and output the proper status code and messaging.
  public static function done( int $code, string $message = null, array $response = [], $flags = null ) {
    
    // Get the response status.
    $status = static::$statuses[$code];
    
    // Return the output if the flag is set.
    if( $flags & static::INDEXAPI_RESPONSE_RETURN ) {
      
       // Build the response.
      $response = array_merge([
        'code' => $code,
        'status' => $status,
        'message' => $message
      ], $response);
      
      // Return the response.
      return $response;
      
    }
    
    // Otherwise, return standard API/CLI responses.
    else {
      
      // Get the exit code.
      $exit = $code > 400 ? 1 : 0;

      // Return a CLI response.
      if( static::$mode == 'CLI' ) {

        // Get the exit code.
        $exit = $code > 400 ? 1 : 0;

        // Get the response status.
        $status = static::$statuses[$code];

        // Get the I/O stream.
        $stream = static::$streams[$exit];

        // Write to the designated stream if available.
        if( $stream ) {

          // Write the message.
          fwrite($stream, $message.PHP_EOL);
          
          // Build the response.
          $response = array_merge($response, (BENCHMARKING ? [
            'performance' => json_decode((Performance::export())->toJson(), true)
          ] : []));

          // Also, output any response data.
          if( !empty($response) ) fwrite($stream, var_export($response, true).PHP_EOL);

        }

      }

      // Otherwise, return an API response.
      else {

        // Get the exit code.
        $exit = $code > 400 ? 1 : 0;

        // Get the response status.
        $status = static::$statuses[$code];

        // Set the content type header to expect a JSON response.
        header('Content-Type: application/json');

        // Set the response code header.
        http_response_code($code);

        // Build the response.
        $response = array_merge([
          'code' => $code,
          'status' => $status,
          'message' => $message
        ], $response, (BENCHMARKING ? [
          'performance' => json_decode((Performance::export())->toJson(), true)
        ] : []));

        // Output any response data as JSON.
        echo json_encode($response, JSON_PRETTY_PRINT);

      }

      // Exit.
      exit($exit);
      
    }
    
  }
  
  // Process a request and output a response.
  public function response( string $method = null, string $endpoint = null ) {
    
    // Use the initialized method and endpoint by default.
    if( !isset($method) ) $method = static::$method;
    if( !isset($endpoint) ) $endpoint = static::$endpoint;
    
    // Convert the method to lowercase.
    $method = strtolower($method);
    
    // Process the request and return its response.
    return static::$method($endpoint);
    
  }
  
  //*********** PROTECTED METHODS ***********//
  
  // Initialize the API.
  protected static function init() {
    
    // Initialize streams.
    if( !isset(static::$streams[0]) ) static::$streams[0] = defined('STDIN') ? STDIN : false;
    if( !isset(static::$streams[1]) ) static::$streams[1] = defined('STDERR') ? STDERR : false;
    
  }
  
  // Parse some data about a request.
  protected static function parse( string $method, string $endpoint ) {
    
    // Make sure an endpoint was given, or respond with an error.
    if( !isset($endpoint) or $endpoint === '' ) return static::done(400, 'An API endpoint was missing from your request.');
    
    // Get the list of registered endpoints.
    $endpoints = static::$endpoints[$method];
    
    // Find the endpoint that the request targeted.
    $target = array_last(array_values(array_filter(array_keys($endpoints), function($e) use ($endpoint) {
      
      // Find the requested endpoint.
      return str_starts_with($endpoint, $e);

    })));
    
    // Parse the request.
    $request = [
      'endpoint' => $target,
      'path' => isset($target) ? ltrim_substr($endpoint, $target) : null,
      'method' => isset($target) ? $endpoints[$target] : false
    ];
    
    // Return the parsed request data.
    return $request;
    
  }
  
  //*********** GET METHODS ***********//
  
  /* Get some data from the index.
   *
   * @example /status - Retrieves the indexing status
   */
  public static function get( string $endpoint, $flags = null ) {
    
    // Initialize the API.
    self::init();

    // Parse the request.
    $request = static::parse('GET', $endpoint);
    
    // For unknown endpoints, respond with an error.
    if( !$request['method'] ) return static::done(405, "Endpoint 'GET $endpoint' does not exist.");
    
    // Then, forward the request to the API endpoint's appropriate method for processing.
    return static::{$request['method']}($request['path'], $flags);
    
  }
  
  // Get the indexing status.
  protected static function getStatus() {
    
    // Get the flags.
    $flags = array_last(func_get_args());
    
    // Determine if the indexing process is currently locked.
    $locked = Index::getLockStatus();
    
    // Initialize the response.
    $response = [
      'state' => ($status = $locked ? 'INDEXING' : 'READY'),
      'locked' => $locked ? true : false,
      'owner' => $locked !== false ? $locked : null
    ];
    
    // Return the response.
    return static::done(200, "The indexer is $status.", $response, $flags);
    
  }
  
  //*********** POST METHODS ***********//
  
  /* Create some data for the index.
   *
   * @example /status - Creates or updates the indexing status
   */
  public static function post( string $endpoint, $flags = null ) {
    
    // Initialize the API.
    self::init();

    // Parse the request.
    $request = static::parse('POST', $endpoint);
    
    // For unknown endpoints, respond with an error.
    if( !$request['method'] ) return static::done(405, "Endpoint 'POST $endpoint' does not exist.");
    
    // Then, forward the request to the API endpoint's appropriate method for processing.
    return static::{$request['method']}($request['path'], $flags);
    
  }
  
  // Create or update the index.
  protected static function createIndex() {
    
    // Use existing options, or initalize them now.
    $options = static::$options ?? new Options();

    // Fail immediately if missing any options, or invalid options were given.
    if( !isset($options->site) ) return static::done(400, 'Missing site parameter.');
    if( !isset($options->environment) ) return static::done(400, 'Missing environment parameter.');
    if( !in_array($options->site, SITES) ) return static::done(400, 'Invalid site.');
    if( !isset($options->username) ) return static::done(401, 'The request you are trying to make requires authentication.');
    if( !isset($options->password) ) return static::done(401, 'The request you are trying to make requires authentication.');
    if( $options->username !== getenv('INDEX_USERNAME') ) return static::done(401, 'Invalid username or password.');
    if( $options->password !== getenv('INDEX_PASSWORD') ) return static::done(401, 'Invalid username or password.');
    
    // Run the indexer.
    $index = new \Index($options);
    
    // Initialize the response.
    $response = $index::$output;
    
    // Return the response.
    return static::done(200, 'Indexing completed succesfully.', $response);
    
  }
  
}

?>