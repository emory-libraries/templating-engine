<?php

/*
 * Route
 *
 * Interprets some route data given the path to a data file.
 */
class Route {
  
  // The path of the data file.
  public $path = null;
  
  // The route's anticipated cache location.
  public $cache;
  
  // The route ID.
  public $id;
  
  // The site's environment.
  public $environment = CONFIG['__site__']['environment'];
  
  // The site ID.
  public $site = CONFIG['__site__']['site'];
  
  // The site's domain.
  public $domain = CONFIG['__site__']['domain'];
  
  // The route's anticipated URL(s).
  public $url;
  
  // Indicates if a route is for an asset.
  public $asset = false;
  
  // Indicates if a route is for an error page.
  public $error = false;
  
  // Indicates if the route redirects, if known.
  public $redirect;
  
  // The route's endpoint.
  public $endpoint;
  
  // The mime type of an asset, if applicable.
  public $mime;
  
  // Constructs the route.
  function __construct( $path ) {
  
    // Infer route data if a path is given.
    if( is_string($path) ) {
    
      // Save the data file path.
      $this->path = $path;

      // Get the data file's ID.
      $this->id = self::id($path);

      // Determine the endpoint that would map to the data file.
      $this->endpoint = self::endpoint($path);
      
      // Get the route's URL(s).
      $this->url = self::url($this->endpoint);
      
      // Determine if the route is an asset.
      if( self::isAsset($path) ) {
        
        // Get the asset's extension.
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        // Set the asset flag to true.
        $this->asset = true;

        // Make sure the the asset's ID, endpoint, and URL includes its extension.
        $this->id .= ".$ext";
        $this->endpoint .= ".$ext";
        $this->url .= ".$ext";
        
        // Get the asset's mime type.
        $this->mime = Mime::type($ext);

      }
      
      // Determine the route's cache location.
      $this->cache = self::cache($this->endpoint);
      
      // Determine if the route is for an error page by assuming pages with integer IDs are errors.
      if( self::isError($this->endpoint) ) $this->error = true;
      
    }
    
    // Otherwise, extract the route data if an array is given.
    else if( is_array($path) ) {
      
      // Extract the route's data from the array.
      $this->endpoint = self::indexIsOptional($path['endpoint']);
      $this->path = array_get($path, 'path');
      $this->id = array_get($path, 'id', File::id($path['endpoint']));
      $this->redirect = array_get($path, 'redirect');
      
      // Get the route's URL(s).
      $this->url = self::url($this->endpoint);

      // Determine if the route is an asset.
      if( self::isAsset($path['endpoint']) ) {
        
        // Get the asset's extension.
        $ext = pathinfo($path['endpoint'], PATHINFO_EXTENSION);

        // Set the asset flag to true.
        $this->asset = true;

        // Make sure the the asset's ID, endpoint, and URL includes its extension.
        $this->id .= ".$ext";
        $this->endpoint .= ".$ext";
        $this->url .= ".$ext";
        
        // Get the asset's mime type.
        $this->mime = Mime::type($ext);

      }
      
      // Determine the route's cache location.
      $this->cache = self::cache($this->endpoint);
      
      // Determine if the route is for an error page by assuming pages with integer IDs are errors.
      if( self::isError($this->endpoint) ) $this->error = true;
      
    }
    
  }
  
  // Get ID of a route given a path.
  public static function id( $path ) { return File::id($path); }
  
  // Get the endpoint of a route given a path.
  public static function endpoint( $path ) { 
    
    // Get the endpoint.
    $endpoint = File::endpoint($path, [
      CONFIG['engine']['root'],
      CONFIG['data']['site']['root'],
      CONFIG['site']['root']
    ]); 
    
    // Return the endpoint.
    return self::indexIsOptional($endpoint);
  
  }
  
  // Make the index keyword optional for an index endpoint.
  public static function indexIsOptional( $endpoint ) {
    
    // For index endpoints, make the index keyword optional.
    if( self::isIndex($endpoint) ) return [
      preg_replace('/index$/', '', $endpoint),
      preg_replace('/index$/', '', $endpoint).'index'
    ];
    
    // Otherwise, for all other endpoints, use it as is.
    return $endpoint;
    
  }
  
  // Get the URL of a route given an endpoint.
  public static function url( $endpoint, $domain = null ) {
    
    // Infer the domain if not given.
    if( !isset($domain) ) {
      
      if( defined('LOCALHOST') and LOCALHOST ) $domain = 'localhost/'.ltrim(str_replace(DOCUMENT_ROOT, '', SITE_ROOT), '/');
      
      else if( defined('NGROK') and NGROK ) $domain = $_SERVER['HTTP_HOST'];
      
      else if( defined('DEVELOPMENT') and DEVELOPMENT ) $domain = 'localhost/templating-engine/public/'.DOMAIN;
      
      else $domain = DOMAIN;
      
    }
    
    // Initialize a helper method for building URLs.
    $url = function( $endpoint ) use ($domain) {
      
      // Get the antipacted URL for the route.
      return Request::protocol().'://'.$domain.$endpoint;
      
    };
    
    //  Return the URL(s) for the route.
    return (is_array($endpoint) ? array_map($url, $endpoint) : $url($endpoint));
    
  }
  
  // Get the cache location of a route given an endpoint.
  public static function cache( $endpoint ) {

    // For array endpoints, use the full endpoint rather than any shorthands.
    if( is_array($endpoint) ) $endpoint = array_values(array_filter($endpoint, function($endpoint) {
      
      // Ignore shorthand endpoint syntaxes.
      return !str_ends_with($endpoint, '/');
      
    }))[0];
    
    // Get the base cache location.
    $base = CONFIG['engine']['cache']['pages'];
    
    // Derive the cache file name.
    $file = preg_replace('/\.'.pathinfo($endpoint, PATHINFO_EXTENSION).'$/', '', $endpoint).'.php';
    
    // Return the cache path.
    return $base.'/'.ltrim($file, '/');
    
  }
  
  // Determine if a route is an asset given an path.
  public static function isAsset( $path ) {
    
    // Get the path's extension.
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    
    // Get a list of all known data files extensions.
    $exts = array_merge(...array_values(Transformer::$transformers));
    
    // Determine if the route is an asset.
    return (isset($ext) and $ext !== '' and !in_array($ext, $exts));
    
  }
  
  // Determine if a route is for an error page given an endpoint.
  public static function isError( $endpoint ) {
    
    // Ignore array endpoints.
    if( is_array($endpoint) ) return false;
    
    // Get the ID.
    $id = basename($endpoint);
    
    // Assume error pages always use integer page IDs, and determine if the route ID is an integer.
    return (((string) ((int) $id)) == $id);
    
  }
  
  // Determine if a route's endpoint is for an endpoint.
  public static function isIndex( $endpoint ) {
    
    // Determine if the ID is for an index endpoint.
    return (str_ends_with($endpoint, 'index') or str_ends_with($endpoint, '/'));
    
  }
  
  // Defines set state method for restoring state.
  public static function __set_state( array $state ) {
    
    // Initialize an instance of the class.
    $instance = new self(null);
    
    // Assign properties to the instance.
    foreach( $state as $property => $value ) { $instance->$property = $value; }
    
    // Return the instance.
    return $instance;
    
  }
  
}

?>