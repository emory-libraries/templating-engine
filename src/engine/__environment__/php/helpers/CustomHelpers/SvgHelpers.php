<?php

namespace CustomHelpers;

trait SvgHelpers {

  // Dynamically load an icon by ID.
  public static function icon( $id ) {
    
    // Find the icon by exact ID.
    $icon = array_get(CONFIG['icons'], $id);
    
    // Otherwise, try to find the target icon file by near ID.
    if( !isset($icon) ) {
      
      // Try to find the icon with the next closest ID.
      foreach(CONFIG['icons'] as $icon => $svg) {
        
        // See if the icon's ID is a near match, and if so, use it.
        if( strpos($icon, $id) !== false ) {
          
          // Capture the icon's SVG.
          $icon = $svg;
          
          // Quit searching for an icon.
          break;
          
        }
        
      }
        
    }
    
    // Return the icon.
    return $icon;
    
  }
  
  // Dynamically load a logo by ID.
  public static function logo( $id ) {
    
    // Find the logo by exact ID.
    $logo = array_get(CONFIG['logos'], $id);
    
    // Otherwise, try to find the target logo file by near ID.
    if( !isset($logo) ) {
      
      // Try to find the logo with the next closest ID.
      foreach(CONFIG['logos'] as $logo => $svg) {
        
        // See if the logo's ID is a near match, and if so, use it.
        if( strpos($logo, $id) !== false ) {
          
          // Capture the logo's SVG.
          $logo = $svg;
          
          // Quit searching for an logo.
          break;
          
        }
        
      }
        
    }
    
    // Return the logo.
    return $logo;
    
  }
  
}

?>