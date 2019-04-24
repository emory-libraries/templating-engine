<?php

namespace CustomHelpers;

trait LocationHelpers {

  // Get the base URL for the given site.
  public static function baseUrl( ) {
    
    // Return the site's base URL.
    return cleanpath('/'.str_replace(CONFIG['document']['root'], '', CONFIG['site']['root']));
    
  }
  
}

?>