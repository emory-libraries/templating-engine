<?php

namespace CustomHelpers;

trait LocationHelpers {

  // Get the base URL for the given site.
  public static function baseUrl( ) {

    // Determine the base path based on whether development mode is enabled.
    //$base = (CONFIG['localhost'] or CONFIG['ngrok']) ? '/'.str_replace(CONFIG['document']['root'], '', CONFIG['site']['root']) : '';

    $base = (CONFIG['localhost'] or CONFIG['ngrok']) ? ''.str_replace(CONFIG['document']['root'], '', CONFIG['site']['root']) : '';

    // Return the site's base URL.
    return cleanpath($base);

  }

}

?>