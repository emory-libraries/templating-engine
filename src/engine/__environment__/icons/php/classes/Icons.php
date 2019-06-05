<?php

// Icons - Loads our icon set.
class Icons {
  
  // Capture icons.
  protected $icons = [];
  
  // Set directory location relative to the root of the icon folder.
  private $dir = '/svg';
  
  // Constructor
  function __construct() {
    
    // Get the root path of the icons folder.
    $root = dirname(dirname(__DIR__));
    
    // Get the icon folder path.
    $path = $root.'/'.ltrim($this->dir, '/');
    
    // Read all icons from the directory.
    $this->icons = array_values(array_filter(scandir($path), function($svg) {
      
      // Only use SVG icons.
      return pathinfo($svg, PATHINFO_EXTENSION) == 'svg';
      
    }));
    
  }
  
  // Getter
  function __get( $id ) {
    
    // Try to retrieve icons by exact ID.
    $icon = array_values(array_filter($this->icons, function($svg) use ($id) {
      
      // Find the icon file with the given ID.
      return "{$id}.svg" == $svg;
      
    }));
    
    // Otherwise, try to retrieve icons by near ID.
    if( empty($icon) ) {
      
      // Retrieve icons by near ID.
      $icon = array_values(array_filter($this->icons, function($svg) use ($id) {

        // Otherwise, find the icon file with the closest ID.
        return preg_match("/^{$id}/", $svg);

      }));
      
    }
                         
    // Ignore invalid icons.
    if( empty($icon) ) return;
    
    // Otherwise, return the icon.
    return new Icon("{$this->dir}/{$icon[0]}");
    
  }
  
}

?>