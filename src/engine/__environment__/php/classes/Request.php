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
  
  // The path of the request.
  public $path;
  
  // The endpoint of the request.
  public $endpoint;
  
  // The query string parameters passed along with the request.
  public $params = [];
  
  // The requested site's environment.
  public $environment;
  
  // The requested site.
  public $site;
  
  // Constructs the request.
  function __construct() {
    
    // Get data about the request.
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->protocol = array_get($_SERVER, 'HTTPS', false) === 'on' ? 'https' : 'http';
    $this->domain = CONFIG['__site__']['domain'];
    $this->site = CONFIG['__site__']['site'];
    $this->environment = CONFIG['__site__']['environment'];
    $this->path = str_replace((isset(CONFIG['server']['path']) ? CONFIG['server']['path'].'/' : '').$this->domain.'/', '', $_SERVER['REQUEST_URI']);
    $this->endpoint = explode('?', $this->path)[0];
    $this->params = $this->method == 'POST' ? (isset($_POST) ? $_POST : []) : (isset($_GET) ? $_GET : []);
    $this->url = "{$this->protocol}://{$this->domain}{$this->path}";
    
  }
  
}

?>