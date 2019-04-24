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
  
  // The asset endpoint within the site.
  public $endpoint;
  
  // The asset's ID.
  public $id;
  
  // Constructs the asset.
  function __construct( $path ) {
    
    // Save the file path.
    $this->path = $path;
    
    // Get the asset type.
    $this->type = pathinfo($path, PATHINFO_EXTENSION);
    
    // Get the asset ID.
    $this->id = basename($path);
    
    // Get the asset's endpoint.
    $this->endpoint = File::endpoint($path, [
      CONFIG['site'],
      CONFIG['engine'],
      CONFIG['data']['site']['root']
    ]).".{$this->type}";
    
  }
  
}

?>