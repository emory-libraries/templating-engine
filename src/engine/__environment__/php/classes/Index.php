<?php

/*
 * Index
 *
 * This indexes all of the site data and templates.
 */
class Index {
  
  // An index of all environment data.
  public $environment = [];
  
  // An index of all site data.
  public $site = [];
  
  // An index of all known patterns.
  public $patterns = [];
  
  // The assets found within the site.
  public $assets = [];
  
  // The index of known routes within the site.
  public $routes = [];
  
  // Defines flags for indexing modes.
  const INDEX_ONLY = 1;
  const INDEX_METADATA = 2;
  const INDEX_READ = 4;
  
  // Constructs the index.
  function __construct() {
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Index', true);
    
    // Get an index of all environment-wide data files, and cache it.
    $this->environment = [
      'metadata' => ($environment = $this->getEnvironmentData(self::INDEX_METADATA)),
      'data' => array_map(function($files) {
       
        // Read all environment data files.
        return self::read($files, 'Data');
        
      }, $environment)
    ];

    // Get an index of all site-wide data files, and cache it.
    $this->site = [
      'metadata' => ($site = $this->getSiteData(self::INDEX_METADATA)),
      'data' => array_map(function($files) {
       
        // Read all site data files.
        return self::read($files, 'Data');
        
      }, $site)
    ];
    
    // Mutate the site data.
    $this->site['data']['site'] = self::mutate($this->site['data']['site']);
    
    // Get an index of all patterns, and cache it.
    $this->patterns = [
      'metadata' => ($patterns = $this->getPatternData(self::INDEX_METADATA)),
      'data' => array_map(function($files) {
       
        // Read all pattern files.
        return self::read($files, 'Pattern');
        
      }, $patterns)
    ];
    
    // Get an index of all assets, and cache it.
    $this->assets = [
      'metadata' => ($assets = $this->getAssetData(self::INDEX_METADATA)),
      'data' => array_combine(array_keys($assets), array_map(function($file) {
       
        // Read all pattern files.
        return self::read($file, 'Asset');
        
      }, array_keys($assets))), 
    ];
    
    // Get routes from the site and asset indices, and cache it.
    $this->routes = $routes = $this->getRouteData($site, $assets);
    
    // Cache everything.
    self::cache('environment', $this->environment);
    self::cache('site', $this->site);
    self::cache('patterns', $this->patterns);
    self::cache('assets', $this->assets);
    self::cache('routes', $this->routes);
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::finish('Index');
  
  }
  
  // Scan a directory for files.
  public static function scan( string $path, $recursive = true ) {
    
    // Verify that the directory exists, and scan it.
    if( File::isDirectory($path) ) return ($recursive ? scandir_recursive($path, $path) : array_map(function($file) use ($path) {
      
      // Make sure the path is absolute.
      return "$path/$file";
      
    }, scandir_clean($path)));
    
    // Otherwise, return no directory contents.
    return [];
    
  }
  
  // Get metadata for one or more files.
  protected static function metadata( $files ) {
    
    // Require that files be given in the form of a string or array.
    if( !is_string($files) and !is_array($files) ) return false;
    
    // Get metadata for a single file.
    if( is_string($files) ) return File::metadata($files);
    
    // Otherwise, get metadata for an array of files.
    else {
      
      // For associative arrays, assume keys are file paths, and replace their values with metadata.
      if( is_associative_array($files) ) {
        
        $files = array_map(function($file) {
          
          // Get the file's metadata.
          return File::metadata($file);

        }, $files);
        
      }
      
      // Otherwise, assume the array values are file paths, and lookup their metadata associatively.
      else {
        
        // Make the array associative, where keys are file paths and values are metadata. 
        $files = array_reduce($files, function($result, $file) {
          
          // Get the file's metadata, and save it.
          $result[$file] = File::metadata($file);

          // Continue reducing.
          return $result;

        }, []);
        
      }
      
      // Return the files with their metadata.
      return $files;
      
    }
    
  }
  
  // Read one or more files.
  protected static function read( $files, $class = null ) {
    
    // Require that files be given in the form of a string or array.
    if( !is_string($files) and !is_array($files) ) return false;
    
    // Initialize a helper method to read the file.
    $read = function($path) use ($class) {
     
      // Use the given class to read the file, or simply get the file's contents.
      return ((isset($class) and class_exists($class)) ? new $class($path) : File::read($path));
      
    };
    
    // Read a single file.
    if( is_string($files) ) return $read($files);
    
    // Otherwise, read an array of files.
    else {
      
      // For associative arrays, assume keys are file paths, and replace their values with contents.
      if( is_associative_array($files) ) array_walk($files, function(&$value, $key) use ($read) {
        
        // Get the file contents.
        $value = $read($key);
        
      });
    
      // Otherwise, assume the array values are file paths, and retrieve their contents associatively.
      else $files = array_reduce($files, function($result, $file) use ($read) {
          
          // Get the file's metadata, and save it.
          $result[$file] = $read($file);

          // Continue reducing.
          return $result;

        }, []);
      
      // Return the files with their contents.
      return $files;
      
    }
    
  }
  
  // Mutate one or more files.
  protected static function mutate( $files ) {
    
    // Get a list of page types with their respective template IDs.
    $types = array_flip(CONFIG['config']['template']);
    
    // Initialize a helper method for mutating a file's data.
    $mutate = function( Data $data ) use ($types) {
      
      // Get the pattern's ID.
      $id = array_get($types, array_get($data->data, '@attributes.definition-path'));
        
      // Mutate the contents based on its ID.
      if( isset($id) ) $data->data = Mutator::mutate($data->data, $id);
      
      // Return the mutated or unmutated data.
      return $data;
      
    };
    
    // Mutate a single file.
    if( is_string($files) ) return $mutate($files);
    
    // Otherwise, mutate an array of files.
    else {
      
      // Mutate the data for each file.
      array_walk($files, function(&$contents, $file) use ($mutate) {
        
        // Mutate the file's contents.
        $contents = $mutate($contents);
        
      });
      
      // Return the mutated data files.
      return $files;
      
    }
    
  }
  
  // Cache some index data.
  protected static function cache( string $name, array $index ) {
    
    // For indices containing both metadata and data, cache the metadata and data separately.
    if( count(array_diff(array_keys($index), ['metadata', 'data'])) === 0 ) {
      
      // Cache the metadata and data separately.
      Index::cache($name, $index['data']);
      Index::cache("$name.metadata", $index['metadata']);
      
    }
    
    // Otherwise, cache the index.
    else {
      
      // Get the index's filename with the proper extension.
      $filename = "$name.php";

      // Convert the index to a PHP string.
      $php = '<?php return '.var_export($index, true).'; ?>';

      // Try to save the index as a temporary file, and make sure no errors were thrown.
      try {

        // Create the temporary file.
        $cached = Cache::tmp($php, $filename);

        // Move the temporary file to the index, and overwrite any existing index file that's there.
        $cached['move'](CONFIG['engine']['cache']['index']."/$filename");

      } 

      // Otherwise, log that an error occurred when attempting to cache the index.
      catch( Exception $e ) { 

        // Log the error.
        error_log("Something went wrong while trying to create the index '$name'.");

      }
      
    }
    
  }
  
  // Locate environment-specific data files.
  protected function getEnvironmentData( $flag = Index::INDEX_ONLY ) {
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Indexing environment data...');
    
    // Get environment-specific data files.
    $environment = [
      'meta' => array_merge(CONFIG['meta'], Index::scan(CONFIG['data']['environment']['meta'])),
      'global' => Index::scan(CONFIG['data']['environment']['global']),
      'shared' => array_merge(Index::scan(CONFIG['data']['environment']['shared']), ...array_values(CONFIG['data']['shared']))
    ];
    
    // Get data file extensions.
    $exts = array_merge(...array_values(Transformer::$transformers));
    
    // Filter out any non-data files from the environment data.
    $environment = array_map(function($data) use ($exts) {
      
      // Filter out any non-data files.
      return array_values(array_filter($data, function($file) use ($exts) {
        
        // Get the file extension.
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        
        // Ignore files that do not have a data file extension.
        return in_array($ext, $exts);
        
      }));
      
    }, $environment);
    
    // Return the files with their contents.
    if( $flag & Index::INDEX_READ ) {
      
      // Read all environment data files.
      $environment = array_map('Index::read', $environment);
    
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
    
      // Return all files with their contents.
      return $environment;
      
    }
    
    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {
      
      // Get metadata for all environment data files.
      $environment = array_map('Index::metadata', $environment);
    
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
    
      // Return all files with their metadata.
      return $environment;
      
    }
    
    // Otherwise, return only the file listing.
    else {
      
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
      
      // Return the file listing.
      return $environment;
      
    }
    
  }
  
  // Locate site-specific data files.
  protected function getSiteData( $flag = Index::INDEX_ONLY ) {
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Indexing site data...');
    
    // Get site-specific data files.
    $site = [
      'meta' => Index::scan(CONFIG['data']['site']['meta']),
      'global' => Index::scan(CONFIG['data']['site']['global']),
      'shared' => Index::scan(CONFIG['data']['site']['shared']),
      'site' => Index::scan(CONFIG['data']['site']['root'])
    ];
    
    // Get data file extensions.
    $exts = array_merge(...array_values(Transformer::$transformers));
    
    // Filter out any non-data files from the site data.
    $site = array_map(function($data) use ($exts) {
      
      // Filter out any non-data files.
      return array_values(array_filter($data, function($file) use ($exts) {
        
        // Get the file extension.
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        
        // Ignore files that do not have a data file extension.
        return in_array($ext, $exts);
        
      }));
      
    }, $site);
    
    // Capture all global, meta, and shared files.
    $meta = array_merge(...array_values(array_subset($site, 'site', ARRAY_SUBSET_EXCLUDE)));
    
    // Filter out all global, meta, and shared data from the site data.
    $site['site'] = array_values(array_filter($site['site'], function($file) use ($meta) {
      
      // Filter out any meta, global, and shared data files.
      return !in_array($file, $meta);
      
    }));

    // Return the files with their contents.
    if( $flag & Index::INDEX_READ ) {
      
      // Get a list of page types with their respective template IDs.
      $types = array_flip(CONFIG['config']['template']);
      
      // Read all site data files.
      $site = array_map('Index::read', $site);
    
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
    
      // Return all files with their contents.
      return $site;
      
    }
    
    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {
      
      // Get metadata for all site data files.
      $site = array_map('Index::metadata', $site);
    
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
    
      // Return all files with their metadata.
      return $site;
      
    }
    
    // Otherwise, return only the file listing.
    else {
      
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
      
      // Return the file listing.
      return $site;
      
    }
    
  }
  
  // Locate all patterns.
  protected function getPatternData( $flag = Index::INDEX_ONLY ) {
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Indexing pattern data...');
    
    // Get all pattern files.
    $patterns = array_map('Index::scan', CONFIG['patterns']['groups']);
    
    // Return the files with their contents.
    if( $flag & Index::INDEX_READ ) {
      
      // Read all pattern files.
      $patterns = array_map('Index::read', $patterns);
    
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
    
      // Return all files with their contents.
      return $patterns;
      
    }
    
    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {
      
      // Get metadata for all pattern files.
      $patterns = array_map('Index::metadata', $patterns);
    
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
    
      // Return all files with their metadata.
      return $patterns;
      
    }
    
    // Otherwise, return only the file listing.
    else {
      
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
      
      // Return the file listing.
      return $patterns;
      
    }
    
  }
  
  // Locate all assets used by the site.
  protected function getAssetData( $flag = Index::INDEX_ONLY ) {
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Indexing asset data...');
    
    // Get asset files.
    $assets = array_merge(...array_values(array_map(function($path, $recursive) {
      
      // Scan the asset path for files.
      $files = Index::scan($path, $recursive);
      
      // If recursion was disabled, then filter out any directories that were found.
      if( !$recursive ) $files = array_values(array_filter($files, 'is_file'));
      
      // Return the files.
      return $files;
        
    }, array_keys(CONFIG['assets']), CONFIG['assets'])));
    
    // Get data file extensions.
    $exts = array_merge(...array_values(Transformer::$transformers));
    
    // Filter out any data files from the asset data.
    $assets = array_values(array_filter($assets, function($file) use ($exts) {
        
      // Get the file extension.
      $ext = pathinfo($file, PATHINFO_EXTENSION);

      // Ignore files that have a data file extension.
      return !in_array($ext, $exts);

    }));

    // Return the files with their contents.
    if( $flag & self::INDEX_READ ) {
      
      // Read all template data files.
      $assets = Index::read($assets);
    
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
    
      // Return all files with their contents.
      return $assets;
      
    }
    
    // Otherwise, return only the files with their metadata.
    else if( $flag & self::INDEX_METADATA ) {
      
      // Get metadata for all site data files.
      $assets = Index::metadata($assets);
    
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
    
      // Return all files with their metadata.
      return $assets;
      
    }
    
    // Otherwise, return only the file listing.
    else {
      
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::finish();
      
      // Return the file listing.
      return $assets;
      
    }
    
  }
  
  // Identifies all known routes within a site from a precompiled set of indices.
  protected function getRouteData( array $site, array $assets = [] ) {
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Indexing route data...');
    
    // Initialize a helper method for converting a single file or array of files to routes.
    $route = function($files) {
      
      // Convert a single file to a route.
      if( is_string($files) ) return new Route($files);
      
      // Otherwise convert an array of files to routes.
      return array_values(array_map(function ($key, $value) {
        
        // Convert the file to a route.
        return new Route((is_string($key) ? $key : $value));

      }, array_keys($files), $files));
      
    };
    
    // Get site-specific data.
    $site = $site['site'];
    
    // Get site routes.
    $site = $route($site);
    
    // Get asset routes.
    $assets = $route($assets);
    
    // Also get any preconfigured routes found within the templating engine itself.
    $engine = $route(array_get(CONFIG['config'], 'routes', []));
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::finish();
    
    // Merge and return all routes.
    return array_merge($engine, $site, $assets);
    
  }
  
}

?>