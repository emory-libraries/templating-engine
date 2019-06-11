<?php

/*
 * Request
 *
 * This analyzes the incoming request and returns 
 * information about the requested endpoint.
 */
class Request {
  
  // The request method.
  public $method = 'GET';
  
  // The requested URL.
  public $url;
    
  // The HTTP protocol used for the request.
  public $protocol;
  
  // The requested site's domain.
  public $domain;
  
  // The path portion of the request.
  public $path;
  
  // The endpoint of the request.
  public $endpoint;
  
  // The query string parameters passed along with the request.
  public $params = [];
  
  // The requested site's environment.
  public $environment;
  
  // The requested site.
  public $site;
  
  // Defines recognized request methods.
  public static $methods = [
    'GET',
    'POST',
    'PUT',
    'DELETE'
  ];
  
  // Constructs the request.
  function __construct( $method = null, $path = null ) {
    
    // Get data about the request.
    $this->method = self::method($method);
    $this->protocol = self::protocol();
    $this->domain = self::domain();
    $this->site = self::site();
    $this->environment = self::environment();
    $this->path = self::path($path);
    $this->endpoint = self::endpoint($path);
    $this->params = self::params($method);
    $this->url = self::url($path);
    
  }
  
  // Get the request protocol.
  public static function protocol() {
    
    // Return the request protocol.
    return array_get($_SERVER, 'HTTPS', false) === 'on' ? 'https' : 'http';
    
  }
  
  // Get the request method.
  public static function method( $method = null ) { 
    
    // Return the request method.
    return in_array($method, self::$methods) ? $method : $_SERVER['REQUEST_METHOD']; 
  
  }
  
  // Get the request domain.
  public static function domain() {
    
    // Return the request domain.
    return DOMAIN;
    
  }
  
  // Get the request site.
  public static function site() {
    
    // Return the request site.
    return SITE;
    
  }
  
  // Get the request environment.
  public static function environment() {
    
    // Return the request environment.
    return ENVIRONMENT;
    
  }
  
  // Get the path portion of the request.
  public static function path( $path = null ) {
    
    // Get the server's $base path, if applicable.
    $base = (defined('SERVER_PATH') ? SERVER_PATH.'/' : '').self::domain().'/';
    
    // Otherwise, return the path portion of the request without the server's base path.
    return str_replace($base, '', (isset($path) ? $path : $_SERVER['REQUEST_URI']));
    
  }
  
  // Get the request endpoint.
  public static function endpoint( $path = null ) {
    
    // Get the endpoint without any query parameters.
    return explode('?', self::path($path))[0];
    
  }
  
  // Get the request parameters.
  public static function params( $method = null ) {
    
    // Return request parameters based on the request method.
    return self::method($method) == 'POST' ? (isset($_POST) ? $_POST : []) : (isset($_GET) ? $_GET : []);
    
  }
  
  // Get the request URL.
  public static function url( $path = null ) {
    
    // Return the request URL.
    return self::protocol().'://'.self::domain().self::path($path);
    
  }
  
  // Parse data about a request.
  public static function parse( $method = null, $path = null ) {
    
    // Return data about the request.
    return [
      'method' => self::method($method),
      'protocol' => self::protocol(),
      'domain' => self::domain(),
      'site' => self::site(),
      'environment' => self::environment(),
      'path' => self::path($path),
      'endpoint' => self::endpoint($path),
      'params' => self::params($method),
      'url' => self::url($path)
    ];
    
  }
  
}

?>