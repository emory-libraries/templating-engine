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
  
  // Constructs the route.
  function __construct( $path ) {
  
    // Infer route data if a path is given.
    if( is_string($path) ) {
    
      // Save the data file path.
      $this->path = $path;

      // Get the data file's ID.
      $this->id = File::id($path);

      // Determine the endpoint that would map to the data file.
      $this->endpoint = File::endpoint($path, [
        CONFIG['engine']['root'],
        CONFIG['data']['site']['root'],
        CONFIG['site']['root']
      ]);
      
      // Make the `index` keyword optional for index endpoints.
      if( $this->id == 'index' or str_ends_with($this->endpoint, '/') ) $this->endpoint = [
        preg_replace('/index$/', '', $this->endpoint),
        preg_replace('/index$/', '', $this->endpoint).'index'
      ];
      
      // Get the route's URL(s).
      $this->url = is_array($this->endpoint) ? array_map(function($endpoint) {
        
        // Get the antipacted URL for the route.
        return Request::protocol().'://'.$this->domain.$endpoint;
        
      }, $this->endpoint) : Request::protocol().'://'.$this->domain.$this->endpoint;

      // Get the route's extension.
      $ext = pathinfo($path, PATHINFO_EXTENSION);

      // Get the data files extensions.
      $exts = array_merge(...array_values(Transformer::$transformers));

      // Determine if the route is an asset.
      if( isset($ext) and $ext !== '' and !in_array($ext, $exts) ) {

        // Set the asset flag to true.
        $this->asset = true;

        // Make sure the the asset's endpoint includes its extension.
        $this->endpoint .= ".$ext";

      }
      
      // Determine the route's cache location.
      $this->cache = cleanpath(CONFIG['engine']['cache']['pages'].'/'.str_replace(".$ext", '', (is_array($this->endpoint) ? array_last($this->endpoint) : $this->endpoint)).'.php');
      
      // Determine if the route is for an error page by assuming pages with integer IDs are errors.
      if( (string) ((int) $this->id) == $this->id ) $this->error = true;
      
    }
    
    // Otherwise, extract the route data if an array is given.
    else if( is_array($path) ) {
      
      // Extract the route's data from the array.
      $this->endpoint = $path['endpoint'];
      $this->path = array_get($path, 'path');
      $this->id = array_get($path, 'id', File::id($path['endpoint']));
      
      // Make the `index` keyword optional for index endpoints.
      if( $this->id == 'index' or str_ends_with($this->endpoint, '/') ) $this->endpoint = [
        preg_replace('/index$/', '', $this->endpoint),
        preg_replace('/index$/', '', $this->endpoint).'index'
      ];

      // Get the route's extension.
      $ext = pathinfo($this->endpoint, PATHINFO_EXTENSION);

      // Get the data files extensions.
      $exts = array_merge(...array_values(Transformer::$transformers));

      // Determine if the route is an asset.
      if( isset($ext) and $ext !== '' and !in_array($ext, $exts) ) {

        // Set the asset flag to true.
        $this->asset = true;

        // Make sure the the asset's endpoint includes its extension.
        $this->endpoint .= ".$ext";

      }
      
      // Determine the route's cache location.
      $this->cache = cleanpath(CONFIG['engine']['cache']['pages'].'/'.str_replace(".$ext", '', (is_array($this->endpoint) ? array_last($this->endpoint) : $this->endpoint)).'.php');
      
      // Determine if the route is for an error page by assuming pages with integer IDs are errors.
      if( (string) ((int) $this->id) == $this->id ) $this->error = true;
      
    }
    
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