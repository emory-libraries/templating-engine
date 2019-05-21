<?php

/*
 * Path
 *
 * Utility methods to help with parsing, building, and/or 
 * resolving relative and absolute paths.
 */
class Path {
  
  // The server's root.
  public static $serverRoot;
  
  // The engine's root.
  public static $engineRoot;
  
  // The site's root.
  public static $siteRoot;
  
  // The cache's root.
  public static $cacheRoot;
  
  // The data's root.
  public static $dataRoot;
  public static $dataRootSite;
  
  // The site's domain root, used for URLs.
  public static $domainRoot;
  
  // The site's path root, used for root-relative paths.
  public static $pathRoot;
  
  // Initialize static variables.
  public static function init() {
    
    // Set the server's root.
    if( !isset(self::$serverRoot) ) self::$serverRoot = SERVER_ROOT;
    
    // Set the engine's root.
    if( !isset(self::$engineRoot) ) self::$engineRoot = ENGINE_ROOT;
    
    // Set the site's root.
    if( !isset(self::$siteRoot) ) self::$siteRoot = SITE_ROOT;
    
    // Set the data's root.
    if( !isset(self::$dataRoot) ) self::$dataRoot = DATA_ROOT;
    if( !isset(self::$dataRootSite) ) self::$dataRootSite = SITE_DATA;
    
    // Set the cache's root.
    if( !isset(self::$cacheRoot) ) self::$cacheRoot = CACHE_ROOT;
    
    // Set the domain's root.
    if( !isset(self::$domainRoot) ) {
      
      // Capture the domain root for localhost environments.
      if( defined('LOCALHOST') and LOCALHOST ) self::$domainRoot = 'localhost/'.trim(ltrim_substr( SITE_ROOT, DOCUMENT_ROOT), '/');
      
      // Otherwise, capture the domain root for ngrok environments.
      else if( defined('NGROK') and NGROK ) self::$domainRoot = $_SERVER['HTTP_HOST'];
      
      // Otherwise, capture the domain root for general development environments.
      else if( defined('DEVELOPMENT') and DEVELOPMENT ) self::$domainRoot = 'localhost/templating-engine/public/'.DOMAIN;
      
      // Otherwise, capture the domain root for all other environments.
      else self::$domainRoot = DOMAIN;
      
    }
    
    // Set the path's root.
    if( !isset(self::$pathRoot) ) {
      
      // Capture the path root for localhost environments.
      if( defined('LOCALHOST') and LOCALHOST ) self::$pathRoot = '/'.trim(ltrim_substr(SITE_ROOT, DOCUMENT_ROOT), '/');
      
      // Otherwise, capture the path root for all other environments.
      else self::$pathRoot = '';
      
    }
    
  }
  
  // Convert a given path to a root-relative path.
  public static function toRootRelative( string $path ) {
    
    // Initialize static variables if not already initialized.
    Path::init();
    
    // Parse the path.
    $parsed = Sabre\Uri\parse($path);
    
    // Determine if the path is a URL by checking to see if it has a protocol.
    if( isset($parsed['scheme']) ) {
    
      // If the host path does not match the domain root, then use the URL as is.
      if( $parsed['host'] != self::$domainRoot ) return $path;
      
      // Otherwise, capture only the URI portion of the URL as the path.
      $path = $parsed['path'];
      
    }
    
    // Return the given path as a root-relative path.
    return self::$pathRoot.'/'.ltrim($path, '/');
    
  }
  
  // Convert a given path to a relative path.
  public static function toRelative( string $path ) {
    
    // Initialize static variables if not already initialized.
    Path::init();
    
    // Parse the path.
    $parsed = Sabre\Uri\parse($path);
    
    // Determine if the path is a URL by checking to see if it has a protocol.
    if( isset($parsed['scheme']) ) {
    
      // If the host path does not match the domain root, then use the URL as is.
      if( $parsed['host'] != self::$domainRoot ) return $path;
      
      // Otherwise, capture only the URI portion of the URL as the path.
      $path = $parsed['path'];
      
    }
    
    // Return the given path as a relative path.
    return ltrim(ltrim_substr($path, self::$pathRoot), '/');
    
  }
  
  // Convert a given path to an absolute path, or URL.
  public static function toAbsolute( string $path ) {
    
    // Initialize static variables if not already initialized.
    Path::init();
    
    // Parse the path.
    $parsed = Sabre\Uri\parse($path);
    
    // Determine if the path is a URL by checking to see if it has a protocol.
    if( isset($parsed['scheme']) ) {
    
      // If the host path does not match the domain root, then use the URL as is.
      if( $parsed['host'] != self::$domainRoot ) return $path;
      
      // Otherwise, capture only the URI portion of the URL as the path.
      $path = $parsed['path'];
      
    }
    
    // Return the given path as an absolute path.
    return self::$domainRoot.'/'.ltrim($path, '/');
    
  }
  
  // Resolve the given path(s) to a path relative to the given root directory.
  public static function resolve( ...$paths ) {
    
    // Initialize static variables if not already initialized.
    Path::init();
    
    // Assign a default path if none were given.
    if( !isset($paths) or empty($paths) ) $paths = [self::$serverRoot];
    
    // Initialize the resolved path.
    $resolved = '';
    
    // Resolve each path given.
    foreach( $paths as $path ) {
      
      // Ignore non-string paths.
      if( !is_string($path) ) continue;
      
      // If the path is root-relative, then overwrite the resolved path.
      if( str_starts_with($path, '/') ) $resolved = rtrim($path, '/');
      
      // Otherwise, append the path to the resolved path.
      else $resolved .= '/'.$path;
      
    }
    
    // Get the path segments.
    $segments = explode('/', $resolved);
    
    // Resolve the path's segments.
    foreach( $segments as $index => $segment ) {
          
      // For `.` segments, use the previous segment as is.
      if( $segment === '.' ) {
        
        // Remove the current segment.
        unset($segments[$index]);
        
      }
      
      // For `..` segments, remove the previous segment.
      if( $segment === '..' ) {
        
        // Remove the previous segment.
        if( $index > 0 ) unset($segments[$index - 1]);
        
        // Also, remove the current segment.
        unset($segments[$index]);
        
      }
      
    }
    
    // Return the resolved path.
    return $resolved;
    
  }
  
  // Resolve a given path to a server path.
  public static function resolveServer( string $path ) {
    
    // Treat this as an alias for the `resolve` method.
    return forward_static_call('Path::resolve', self::$serverRoot, $path);
    
  }
  
  // Resolve a given path to a data path on the server.
  public static function resolveData( string $path, $siteSpecific = false ) {
    
    // Get the data root.
    $dataRoot = $siteSpecific ? self::$dataRootSite : self::$dataRoot;
    
    // Treat this as an alias for the `resolve` method.
    return forward_static_call('Path::resolve', $dataRoot, $path);
    
  }
  
  // Resolve a given path to a site path on the server.
  public static function resolveSite( string $path ) {
    
    // Treat this as an alias for the `resolve` method.
    return forward_static_call('Path::resolve', self::$siteRoot, $path);
    
  }
  
  // Resolve a given path to an engine path on the server.
  public static function resolveEngine( string $path, $cacheSpecific = false ) {
    
    // Get the engine root.
    $engineRoot = $cacheSpecific ? self::$cacheRoot : self::$engineRoot;
    
    // Treat this as an alias for the `resolve` method.
    return forward_static_call('Path::resolve', $engineRoot, $path);
    
  }
  
  // Resolve a given path to a cache path on the server.
  public static function resolveCache( string $path ) {
    
    // Treat this as an alias for the `resolve` method.
    return forward_static_call('Path::resolve', self::$cacheRoot, $path);
    
  }
  
  // Convert a given path to an absolute path, or URL.
  public static function toUrl( string $path ) { 
    
    // Treat this as an alias for the `toAbsolute` method.
    return forward_static_call('Path::toAbsolute', $path); 
  
  }
  
  // Get the basename of a path.
  public static function basename( string $path ) { 
    
    // Return the path's basename.
    return basename($path); 
  
  }
  
  // Get the dirname of a path.
  public static function dirname( string $path ) { 
    
    // Return the path's dirname.
    return dirname($path); 
  
  }
  
  // Get the extname of a path.
  public static function extname( string $path ) { 
    
    // Return the path's extname.
    return pathinfo($path, PATHINFO_EXTENSION); 
  
  }
  
  // Get the filename of a path (without an extname).
  public static function filename( string $path ) { 
    
    // Return the path's filename/
    return basename($path, '.'.self::extname($path)); 
  
  }
  
  // Get the desired segment from a path.
  public static function segment( string $path, $segment ) {
    
    // Initialize static variables if not already initialized.
    Path::init();
    
    // Parse the path.
    $parsed = Sabre\Uri\parse($path);
    
    // Alias pieces of the parsed path with additional names.
    $parsed['protocol'] = $parsed['scheme'];
    $parsed['domain'] = $parsed['host'];
    $parsed['params'] = is_array($parsed['query']) ? $parsed['query'] : parse_str($parsed['query']);
    
    // For string segments, get some data from the parsed path.
    if( is_string($segment) ) return array_get($parsed, $segment);
    
    // Otherwise, for integer segments, get an portion of the path.
    return array_get(explode(DIRECTORY_SEPARATOR, trim($parsed['path'], '/')), $segment);
    
  }
  
  // Extract the portion of the path between the given path segments or indices (inclusive).
  public static function slice( string $path, $from, $to = null ) {
    
    // Initialize static variables if not already initialized.
    Path::init();
    
    // If a string was given for `from`, then handle the path using named path segments.
    if( is_string($path) ) {
    
      // Parse the path.
      $parsed = Sabre\Uri\parse($path);

      // Alias pieces of the parsed path with additional names.
      $parsed['protocol'] = $parsed['scheme'];
      $parsed['domain'] = $parsed['host'];

      // Identify the order in which named path parts are arranged.
      $order = [
        ['scheme', 'protocol'], 
        ['host', 'domain'], 
        'port', 
        'path', 
        'query',
        'fragment'
      ];
      
      // Initialize a helper for extracting a segment.
      $segment = function($name) use ($parsed) {
        
        // Initialize the segment.
        $segment = '';
        
        // Get the segment name.
        $name = is_array($name) ? $name[0] : $name;
        
        // Add a prefix for select segment names.
        switch($name) {
          case 'port': 
            $segment .= ':';
            break;
          case 'query':
            $segment .= '?';
            break;
          case 'fragment':
            $segment .= '#';
            break;
        }
        
        // Get the segment by name.
        $segment .= $parsed[$name];
        
        // Add a suffix for select segment names.
        switch($name) {
          case 'scheme': 
            $segment .= '://';
            break;
        }
        
        // Return the segment.
        return $segment;
        
      };
      
      // Ignore invalid `from` arguments.
      if( !isset($parsed[$from]) ) return null;
      
      // Initialize the segments.
      $segments = [$segment($from)];
      
      // Initialize a flag to indicate when capturing should begin.
      $capture = false;
      
      // Extract the segments between `from` and `to`.
      foreach( $order as $named ) { 
        
        // Find the `from` segment, then enable capturing.
        if( (is_array($named) and in_array($from, $named)) or $named == $from ) {
          
          // Enable capturing.
          $capture = true;
          
          // Then, continue.
          continue;
          
        }
        
        // Otherwise, ignore all segments before `from` but capture all segements through `to`.
        else if( $capture ) {
        
          // Otherwise, if a `to` segment was not given, capture all subsequent segments.
          if( !isset($to) ) $segments[] = $segment($named);

          // Otherwise, only capture the segments up to and including the `to` segment.
          else {

            // Capture the segment.
            $segments[] = $segment($named);

            // If the current segment is the `to` segment, then stop extracting segments.
            if( (is_array($named) and in_array($to, $named)) or $named == $to ) break;

          }
          
        }
        
      }
      
      // Return the segments.
      return implode('', $segments);
      
    }
    
    // Otherwise, handle the path using indices.
    else {
      
      // Get the path's segments.
      $segments = explode('/', trim($path, '/'));
      
      // Extract the segments between `from` and `to`.
      $segments = isset($to) ? array_slice($segments, $from, $from - $to) : array_slice($segments, $from);
      
      // Return the extracted slice of the path.
      return implode('/', $segments);
      
    }
    
  }
  
  // Get the relative path from a given point A to point B.
  public static function relative( string $a, string $b = null, $mustExist = false ) {
    
    // Initialize static variables if not already initialized.
    Path::init();
    
    // If point B is not given, use point B as the path and the server's root as point A.
    if( !isset($b) or is_bool($b) ) {
      
      // Make sure the flag is set.
      $mustExist = is_bool($b) ? $b : false;
      
      // Use the given point A as point B.
      $b = $a;
      
      // Then, use the server's root as point A.
      $a = self::$serverRoot;
      
    }
    
    // Initialize helpers for determining if a path is a file or directory.
    $isFile = function($path) use ($mustExist) {
      
      // If the file must exist, then check that the file exists now.
      if( $mustExist ) return File::isFile($path);
      
      // Otherwise, if the path ends with a directory separator, assume it's not a file.
      if( str_ends_with($path, DIRECTORY_SEPARATOR) ) return false;
      
      // Otherwise, see if the path has an extension, and if so, assume it's a file.
      return Path::extname($path) !== null;
      
    };
    $isDirectory = function($path) use ($mustExist) {
      
      // If the directory must exist, then check that the directory exists now.
      if( $mustExist ) return File::isDirectory($path);
      
      // Otherwise, if the path ends with a directory separator, assume it's a directory.
      if( str_ends_with($path, DIRECTORY_SEPARATOR) ) return true;
      
      // Otherwise, see if the path has an extension, and if not, assume it's a directory.
      return Path::extname($path) === null;
      
    };
    
    // Get the directory portion of point A if a file was given.
    if( $isFile($a) ) $a = Path::dirname($a);
    
    // Resolve the paths.
    $resolvedA = Path::resolve($a);
    $resolvedB = Path::resolve($b);
    
    // If the resolved paths must exist but don't, then return false.
    if( $mustExist and (!File::exists($resolvedA) or !File::exists($resolvedB)) ) return false;
    
    // Determine if the resolved paths are equal, and if so, return the relative path as `.`.
    if( $resolvedA == $resolvedB ) return '.';
    
    // Get the resolved path segments.
    $segmentsA = explode('/', trim($resolvedA, '/'));
    $segmentsB = explode('/', trim($resolvedB, '/'));
    
    // Initiailze the relative path segments.
    $relative = [];
    
    // Decipher the relative path between A and B..
    foreach( $segmentsB as $index => $segmentB ) {
      
      // Get the same segment from the resolve path for point A.
      $segmentA = array_get($segmentsA, $index, false);
      
      // If a segment for point A was found, use it to determine the relative path between A and B.
      if( $segmentA ) {
        
        // If the segments are the same, use `..`.
        if( $segmentA == $segmentB ) $relative[] = '..';
        
        // Otherwise, ignore segment A, and use segment B.
        else $relative[] = $segmentB;
        
      }
      
      // Otherwise, capture the segment from point B as is.
      else $relative[] = $segmentB;
      
    }
    
    // Build the relative path, and return it.
    return implode('/', $relative).(str_ends_with($resolvedB, '/') ? '/' : '');
    
  }
  
}

?>