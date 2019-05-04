<?php

/*
 * Asset
 *
 * Parses information about an asset file given
 * an asset path.
 */
class Asset {
  
  // The asset file path.
  public $path;
  
  // The asset's type based on the file extension.
  public $type;
  
  // The asset's mime type.
  public $mime;
  
  // The asset endpoint within the site.
  public $endpoint;
  
  // The asset's ID.
  public $id;
  
  // Constructs the asset.
  function __construct( $path ) {
    
    // Extract asset data given a path.
    if( is_string($path) ) {
    
      // Save the file path.
      $this->path = $path;

      // Get the asset type.
      $this->type = pathinfo($path, PATHINFO_EXTENSION);

      // Get the asset's mime type.
      $this->mime = Mime::type($this->type);

      // Get the asset ID.
      $this->id = basename($path);

      // Get the asset's endpoint.
      $this->endpoint = File::endpoint($path, [
        CONFIG['site']['root'],
        CONFIG['data']['site']['root'],
        CONFIG['engine']['root']
      ]).".{$this->type}";
      
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