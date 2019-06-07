<?php

// Set namespace.
namespace Index;

// Use dependencies.
use Request;

/**
 * Options
 *
 * Parses incoming options for any index requests.
 */
class Options {
  
  // The request method.
  public $method = 'GET';
  
  // The request endpoint.
  public $endpoint = '/';
  
  // Get options.
  public $site = null;
  public $environment = null;
  public $development = false;
  public $username = null;
  public $password = null;
  public $callback = false;
  
  // Set help information.
  protected static $help = [
    [
      'label' => 'index',
      'description' => 'Triggers an index'
    ],
    [
      'label' => ['--development', '-d'],
      'description' => 'Indicates whether the command should run in development mode',
      'required' => false
    ],
    [
      'label' => ['--site', '-s'],
      'description' => 'The site that should be processed',
      'required' => true
    ],
    [
      'label' => ['--environment', '-e'],
      'description' => 'The environment that should be processed',
      'required' => true
    ],
    [
      'label' => ['--username', '-u'],
      'description' => 'The username to be used for authentication',
      'required' => 'only for create and/or update commands'
    ],
    [
      'label' => ['--password', '-p'],
      'description' => 'The password to be used for authentication',
      'required' => 'only for create and/or update commands'
    ],
    [
      'label' => ['--callback', '-c'],
      'description' => 'A callback that should be executed after select commands',
      'required' => false
    ]
  ];
  
  // Constructor.
  function __construct() {
    
    // Determine the options parsing method to be used.
    $this->method = $_SERVER['REQUEST_METHOD'] ?? false;
    
    // Parse the options.
    $this->method ? self::API() : self::CLI();
    
  }
  
  // Parse API options.
  protected function API() {
    
    // Get the API parameters based on the request method.
    $params = $this->method == 'POST' ? $_POST : $_GET;
    
    // Capture API options.
    $this->site = defined(SITE) ? SITE : $params['site'] ?? null;
    $this->environment = defined(ENVIRONMENT) ? ENVIRONMENT : $params['environment'] ?? null;
    $this->development = isset($_POST['development']) ? filter_var($params['development'], FILTER_VALIDATE_BOOLEAN) : false;
    $this->callback = $params['callback'] ?? null;
    
    
    // Also, capture credentials for POST requests.
    if( $this->method == 'POST' ) {
      $this->username = $_SERVER['PHP_AUTH_USER'] ?? null;
      $this->password = $_SERVER['PHP_AUTH_PW'] ?? null;
    }
    
    // Finally, capture the endpoint.
    $this->endpoint = str_replace($_SERVER['SCRIPT_NAME'], '', Request::endpoint());
    
  }
  
  // Parse CLI options.
  protected function CLI() {
    
    // Get CLI parameters.
    $params = parse_argv($_SERVER['argv']);

    // Normalize and save CLI options.
    $this->site = $params['site'] ?? $params['s'];
    $this->environment = $params['environment'] ?? $params['e'];
    $this->development = (isset($params['development']) or isset($params['d'])) ? true : false;
    $this->callback = $params['callback'] ?? $params['c'] ?? false;
    $this->username = $params['username'] ?? $params['u'] ?? null;
    $this->password = $params['password'] ?? $params['p'] ?? null;
    $this->method = $params['method'] ?? $params['m'] ?? 'GET';
    
    // Finally, capture the endpoint.
    $this->endpoint = $params[0];
    
  }
  
  // Return a help menu.
  public static function help() {
    
    // Build the help menu.
    
    
  }
  
}

?>