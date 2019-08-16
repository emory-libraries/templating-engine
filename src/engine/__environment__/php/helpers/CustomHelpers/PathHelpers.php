<?php

namespace CustomHelpers;

trait PathHelpers {

  // Get the base URL for the given site.
  public static function baseUrl( ) {

    // Determine the base path based on whether development mode is enabled.
    $base = (CONFIG['localhost'] or CONFIG['ngrok']) ? ''.str_replace(CONFIG['document']['root'], '', CONFIG['site']['root']) : '';

    // Return the site's base URL.
    return cleanpath($base);

  }

  // Convert a path into an absolute path.
  public static function absolutePath( string $path ) {

    // Convert the path to an absolute path.
    return Path::toAbsolute($path);

  }

  // Convert a path into a relative path.
  public static function relativePath( string $path ) {

    // Convert the path to a relative path.
    return Path::toRelative($path);

  }

  // Convert a path into a root relative path.
  public static function rootRelativePath( string $path ) {

    // Convert the path to a root relative path.
    return Path::toRootRelative($path);

  }

}

?>
