<?php

// Use dependencies.
use Moment\Moment;
use Moment\CustomFormats\MomentJs;

/*
 * Index
 *
 * This indexes all of the site data and templates.
 */
class Index {
  
  // Determine if performance data should be output.
  public $performance = false;
  
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
  
  // The index of all partials.
  public $partials = [];
  
  // The index of all recognized endpoints.
  public $endpoints = [];
  
  // The index of all available handlebars helpers.
  public $helpers = [];
  
  // Indicates when the initial indexing process has completed.
  public $indexed = false;
  
  // The process ID of the current indexing instance.
  public static $pid = null;
  
  // The amount of time given before a lock expires in seconds.
  public static $expires = 30;
  
  // Defines flags for indexing modes.
  const INDEX_ONLY = 1;
  const INDEX_METADATA = 2;
  const INDEX_READ = 4;
  
  // Defines flags that can be used for the merge method.
  const MERGE_PATHS = 1;
  const MERGE_CONTENTS = 2;
  const MERGE_NORMAL = 4;
  const MERGE_RECURSIVE = 8;
  const MERGE_KEYED = 16;
  const MERGE_GROUPED = 32;
  const MERGE_OVERRIDE = 64;
  
  // Constructs the index.
  function __construct( $instance = false ) {
    
    // Set the process ID.
    self::$pid = uniqid(DOMAIN.':', true);
    
    // Save the process ID to the process log.
    self::log(true, self::$pid);
    
    // Determine if the indexing process is locked, and if so, prevent reindexing.
    if( ($owner = self::lock()) !== false and $owner !== self::$pid ) {
      
      // Push the request onto the queue.
      self::queue(true);
      
      // Remove the process from the process log.
      self::log(false, self::$pid);
      
      // Exit.
      done(0, "The indexing process is currently active for '".DOMAIN."'. Your request was added to a queue.", 202);
      
    }
    
    // Set performance flag.
    $this->performance = (!$instance and DEVELOPMENT);
    
     // Add benchmark point.
    if( $this->performance ) Performance\Performance::point('Index', true);
    
    // Lock the indexing process.
    self::lock(true);
    
    // Set a anchor point where reindexing should start if and when triggered.
    reindex:
    
    // Wipe the queue.
    self::queue(false);
    
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
    
    // Build partials based on the pattern index.
    $this->partials = [
      'metadata' => null,
      'data' => ($partials = $this->getPartialData($this->patterns['data']))
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
    $this->routes = [
      'metadata' => null,
      'data' => ($routes = $this->getRouteData($site, $assets))
    ];
    
    // Build endpoints based on the environment, site, pattern, and route indices.
    $this->endpoints = [
      'metadata' => null,
      'data' => ($endpoints = $this->getEndpointData($this->environment['data'], $this->site['data'], $this->patterns['data'], $routes))
    ];
    
    // Get an index of handlebars helpers, and cache it.
    $this->helpers = [
      'metadata' => null,
      'data' => ($helpers = $this->getHelperData())
    ];
    
    // Cache everything.
    if( !$instance ) {
      self::cache('environment', $this->environment);
      self::cache('site', $this->site);
      self::cache('patterns', $this->patterns);
      self::cache('partials', $this->partials);
      self::cache('assets', $this->assets);
      self::cache('routes', $this->routes);
      self::cache('endpoints', $this->endpoints);
      self::cache('helpers', $this->helpers);
    }
    
    // Set the indexed flag.
    $this->indexed = true;
    
    // If additional indexing requests were received while the last process was running, then reindex everything.
    if( !empty(self::queue()) ) goto reindex;
    
    // Unlock the indexing process.
    self::lock(false);
    
    // Remove the process from the process log.
    self::log(false, self::$pid);
    
    // Add benchmark point.
    if( $this->performance ) Performance\Performance::finish('Index');
  
  }
  
  // Call methods as static functions.
  public static function __callStatic( $method, $arguments ) { 
    
    // Get an instance of the class.
    $instance = new self(true);
    
    // Make some protected methods public.
    switch($method) {
      case 'getPartialData': return $instance->partials['data'];
      case 'getHelperData': return $instance->helpers['data'];
      case 'getAssetData': return $instance->assets['data'];
      case 'getEndpointData': return $instance->endpoints['data'];
      case 'getAssetEndpointData': return array_values(array_filter($instance->endpoints['data'], function($endpoint) {

        // Locate all asset endpoints.
        return $endpoint->asset;

      }));
      case 'getAssetEndpoint': return array_get(array_values(array_filter($instance->endpoints['data'], function($endpoint) use ($arguments) {
        
        // Find asset endpoints.
        if( !$endpoint->asset ) return false;

        // Find the endpoint data for the given endpoint path.
        return (is_array($endpoint->endpoint) ? in_array($arguments[0], $endpoint->endpoint) : $endpoint->endpoint == $arguments[0]);

      })), 0);
    }
    
  }
  
  // Scan a directory for files.
  public static function scan( string $path, $recursive = true ) {
    
    // Verify that the directory exists, and scan it.
    if( File::isDirectory($path) ) return ($recursive ? scandir_recursive($path, $path) : array_map(function($file) use ($path) {
      
      // Make sure the path is absolute.
      return cleanpath($path.'/'.ltrim($file, '/'));
      
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
  
  // Merge data files.
  protected static function merge( ...$arrays/*, $flags = self::MERGE_KEYED | self::MERGE_RECURSIVE*/ ) {

    // Get flags, or set the default.
    $flags = !is_array(array_last($arrays)) ? array_last($arrays) : self::MERGE_KEYED | self::MERGE_RECURSIVE;
    
    // Filter out any non-arrays from the data set.
    $arrays = array_values(array_filter($arrays, 'is_array'));
    
    // Merge the data on keys, where keys are composed of to data file IDs.
    if( $flags & self::MERGE_KEYED ) {
      
      // Initialize the result.
      $result = [];
      
      // Merge the data arrays.
      foreach( $arrays as $data ) {
      
        // Merge data by key.
        foreach( $data as $file => $content ) {

          // Derive the key from the file's ID.
          $key = File::id($file);

          // Get the existing data for that key.
          $existing = array_get($result, $key, []);

          // Group the data by key.
          if( $flags & self::MERGE_GROUPED ) {

            // Add the data into the group.
            $result = array_set($result, $key, array_merge([], $existing, [$content->data]));

          }

          // Recursively merge the data into the keyed data.
          else if( $flags & self::MERGE_RECURSIVE ) {

            // Recursively merge the data.
            $result = array_set($result, $key, array_merge_recursive($existing, $content->data));

          }

          // Otherwise, merge the data into the keyed data.
          else if( $flags & self::MERGE_NORMAL ) {

            // Merge that data normally.
            $result = array_set($result, $key, array_merge($existing, $content->data));

          }

          // Otherwise, set and/or override keyed data.
          else $result = array_set($result, $key, $content->data, ($flags & self::MERGE_OVERRIDE));

        }
        
      }
      
      // Return the result.
      return $result;
      
    }
  
    // Recursively merge the data, and return it. 
    if( $flags & self::MERGE_RECURSIVE ) return array_merge_recursive(...array_values($path));
    
    // Otherwise, merge the data, and return it.
    if( $flags & self::MERGE_NORMAL ) return array_merge(...array_values($path));
    
    // Otherwise, return only the data contents.
    if( $flags & self::MERGE_CONTENTS ) return array_values($path);
    
    // Otherwise, return the data as is with paths included.
    return $path;
    
  }
  
  // Compile the meta data set for a request.
  protected static function compile( array $environment, array $site, array $endpoint ) {
   
    // Get global, meta, and shared data.
    $global = self::merge($environment['global'], $site['global'], self::MERGE_KEYED | self::MERGE_RECURSIVE);
    $meta = self::merge($environment['meta'], $site['meta'], self::MERGE_KEYED | self::MERGE_RECURSIVE);
    $shared = self::merge($environment['shared'], $site['shared'], self::MERGE_KEYED | self::MERGE_GROUPED);
    
    // Merge additional data into the route's endpoint data.
    $data = array_merge([
      '__global__' => $global,
      '__meta__' => $meta,
      '__shared__' => $shared,
      '__params__' => []
    ], $endpoint);
    
    // Return the compiled data.
    return $data;
    
  }
  
  // Cache some index data.
  protected static function cache( string $name, array $index ) {
    
    // Add created and modified times into the cache data.
    $index['modified'] = $index['created'] = new DateTime();
      
    // Get the index's filename with the proper extension.
    $phpFilename = "$name.php";
    $jsonFilename = "$name.json";
    
    // Set the index file's destination path.
    $phpDest = CONFIG['engine']['cache']['index']."/$phpFilename";
    $jsonDest = CONFIG['engine']['cache']['index']."/$jsonFilename";

    // Convert the index to a PHP string and JSON string.
    $php = '<?php return '.var_export($index, true).'; ?>';
    $json = json_encode($index, JSON_PRETTY_PRINT);

    // Try to save the index as a temporary file, and make sure no errors were thrown.
    try {

      // Create the temporary file.
      $phpTmp = Cache::tmp($php, $phpFilename, 0777);
      $jsonTmp = Cache::tmp($json, $jsonFilename, 0777);

      // Move the temporary file to the index, and overwrite any existing index file that's there.
      $phpTmp['move']($phpDest);
      $jsonTmp['move']($jsonDest);

    } 

    // Otherwise, log that an error occurred when attempting to cache the index.
    catch( Exception $e ) { 

      // Log the error.
      error_log("Something went wrong while trying to create the index '$name'.");

    }
    
  }
  
  // Keep a log of all active indexing processes.
  protected static function log( bool $state = null, $pid = null ) {
    
    // Set the processes filename.
    $phpFilename = 'processes.php';
    $jsonFilename = 'processes.json';

    // Set the processes file's destination path.
    $phpDest = CONFIG['engine']['cache']['index']."/$phpFilename";
    $jsonDest = CONFIG['engine']['cache']['index']."/$jsonFilename";

    // If a state was given, then log the process with the given state.
    if( isset($state) ) {

      // Get the current list of running processes if available, or initialize an empty list otherwise.
      $processes = Cache::exists($phpDest) ? Cache::include($phpDest) : [];

      // Add the PID to the process list if the state indicates that it should be added.
      if( $state === true and isset($pid) and !in_array($pid, $processes) ) $processes[] = $pid;

      // Otherwise, remove the PID from the process list.
      else if( $state === false and isset($pid) and in_array($pid, $processes) ) unset($processes[array_search($pid, $processes)]);

      // Convert the processes to a PHP string and JSON string.
      $php = '<?php return '.var_export($processes, true).'; ?>';
      $json = json_encode($processes, JSON_PRETTY_PRINT);

      // Try to save the processes as a temporary file, and make sure no errors were thrown.
      try {

        // Create the temporary file.
        $phpTmp = Cache::tmp($php, $phpFilename, 0777);
        $jsonTmp = Cache::tmp($json, $jsonFilename, 0777);

        // Move the temporary file to the index, and overwrite any existing index file that's there.
        $phpTmp['move']($phpDest);
        $jsonTmp['move']($jsonDest);

      }

      // Otherwise, throw an error if one occurred when attempting to save the list of processes.
      catch( Exception $e ) {

        // Log the error.
        error_log("Something went wrong while trying to create the process index.");

      }
      
    }
    
    // Otherwise, return list of all currently running processes.
    else return (Cache::exists($phpDest) ? Cache::include($phpDest) : []);
    
  }
  
  // Set or get the lock state of the indexing process.
  protected static function lock( bool $state = null ) {
    
    // Get the lock file path.
    $path = CONFIG['engine']['cache']['index'].'/indexing.lock';
    
    // Get the lock status.
    $status = Cache::exists($path);
    
    // If a lock file exists, then determine if it is invalid and/or expired, and adjust the lock status accordingly.
    if( $status ) {
      
      // Get the owner of the cache file and a list of all running processes.
      $owner = Cache::read($path);
      $processes = self::log();
     
      // If the process list is empty or the owner cannot be found within the process list, then assume the lock file is invalid.
      if( empty($processes) or !in_array($owner, $processes) ) {
      
        // Determine the expiry time of the lock file.
        $expiration = Moment::fromDateTime((new DateTime())->setTimestamp(Cache::modified($path)))->addSeconds(self::$expires)->format('X', new MomentJs());
      
        // Determine if the lock file has expired, and if so, unlock the indexing process and update the status.
        if( $expiration < time() ) {
        
          // Unlock the indexing process.
          Cache::delete($path);

          // Update the lock status.
          $status = false;

        }
        
      }
      
    }
    
    // Return the status if a state was not given.
    if( !isset($state) ) {
      
      // If the indexing process is unlocked, then simply return the status as being unlocked.
      if( !$status ) return false;
      
      // Otherwise, if the indexing process is locked, then return the ID of the lock owner.
      else return Cache::read($path);
      
    }
    
    // Otherwise, if state was given, then attempt to lock or unlock the indexing process.
    else {
      
      // Attempt to lock the indexing process if not previously locked.
      if( !$status and $state === true ) Cache::write($path, self::$pid);
        
      // Otheriwse, attempt to unlock the indexing process if previously locked.
      else if( $status and $state === false ) {
        
        // Get the ID of lock file's owner process.
        $owner = Cache::read($path);
        
        // Unlock the indexing process only if the lock file's owner is the current process.
        if( self::$pid === $owner ) Cache::delete($path);
        
      }
      
    }
    
  }
  
  // Queue multiple incoming requests.
  protected static function queue( $q = null ) {
    
    // Set the queue filename.
    $phpFilename = 'queue.php';
    $jsonFilename = 'queue.json';

    // Set the queue file's destination path.
    $phpDest = CONFIG['engine']['cache']['index']."/$phpFilename";
    $jsonDest = CONFIG['engine']['cache']['index']."/$jsonFilename";
    
    // Initialize a helper for saving queue data.
    $save = function( $queue ) use ($phpFilename, $jsonFilename, $phpDest, $jsonDest) {
      
      // Convert the queue to a PHP string and JSON string.
      $php = '<?php return '.var_export($queue, true).'; ?>';
      $json = json_encode($queue, JSON_PRETTY_PRINT);

      // Try to save the queue as a temporary file, and make sure no errors were thrown.
      try {

        // Create the temporary file.
        $phpTmp = Cache::tmp($php, $phpFilename, 0777);
        $jsonTmp = Cache::tmp($json, $jsonFilename, 0777);

        // Move the temporary file to the index, and overwrite any existing index file that's there.
        $phpTmp['move']($phpDest);
        $jsonTmp['move']($jsonDest);

      }

      // Otherwise, throw an error if one occurred when attempting to queue the request.
      catch( Exception $e ) { throw $e; }
      
    };
    
    // If a push was requested, then queue the current request.
    if( $q === true ) {
    
      // Get the current timestamp.
      $timestamp = time();

      // If a queue already exists then get its contents, or initialize an empty queue otherwise.
      $queue = Cache::exists($phpDest) ? Cache::include($phpDest) : [];

      // Add the current request to the queue.
      $queue[] = $timestamp;

      // Try to save the queue as a temporary file, and make sure no errors were thrown.
      try { $save($queue); }

      // Otherwise, log that an error occurred when attempting to queue the request.
      catch( Exception $e ) { error_log("Something went wrong while trying to queue the incoming indexing request at '$timestamp'."); }
      
    }
    
    // Otherwise, if a wipe was requested, then reset the queue.
    else if( $q === false ) {
      
      // Get the queue's contents.
      $queue = [];
      
      // Try to save the queue as a temporary file, and make sure no errors were thrown.
      try { $save($queue); }

      // Otherwise, log that an error occurred when attempting to queue the request.
      catch( Exception $e ) { error_log('Something went wrong while trying to wipe the indexing queue.'); }
      
    }
    
    // Otherwise, get the contents of the queue.
    else return (Cache::exists($phpDest) ? Cache::include($phpDest) : []);
    
  }
  
  // Locate environment-specific data files.
  protected function getEnvironmentData( $flag = Index::INDEX_ONLY ) {
    
    // Add benchmark point.
    if( $this->performance and !$this->indexed ) Performance\Performance::point('Indexing environment data...');
    
    // Get meta data.
    $meta = isset(CONFIG['meta']) ? CONFIG['meta'] : (include CONFIG['engine']['php'].'/config.index.php')['meta'];
    
    // Get shared data.
    $shared = isset(CONFIG['data']['shared']) ? CONFIG['data']['shared'] : (include CONFIG['engine']['php'].'/config.index.php')['data']['shared'];
    
    // Get environment-specific data files.
    $environment = [
      'meta' => array_merge($meta, Index::scan(CONFIG['data']['environment']['meta'])),
      'global' => Index::scan(CONFIG['data']['environment']['global']),
      'shared' => array_merge(Index::scan(CONFIG['data']['environment']['shared']), ...array_values($shared))
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
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
      // Return all files with their contents.
      return $environment;
      
    }
    
    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {
      
      // Get metadata for all environment data files.
      $environment = array_map('Index::metadata', $environment);
    
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
      // Return all files with their metadata.
      return $environment;
      
    }
    
    // Otherwise, return only the file listing.
    else {
      
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
      
      // Return the file listing.
      return $environment;
      
    }
    
  }
  
  // Locate site-specific data files.
  protected function getSiteData( $flag = Index::INDEX_ONLY ) {
    
    // Add benchmark point.
    if( $this->performance and !$this->indexed ) Performance\Performance::point('Indexing site data...');
    
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
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
      // Return all files with their contents.
      return $site;
      
    }
    
    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {
      
      // Get metadata for all site data files.
      $site = array_map('Index::metadata', $site);
    
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
      // Return all files with their metadata.
      return $site;
      
    }
    
    // Otherwise, return only the file listing.
    else {
      
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
      
      // Return the file listing.
      return $site;
      
    }
    
  }
  
  // Locate all patterns.
  protected function getPatternData( $flag = Index::INDEX_ONLY ) {
    
    // Add benchmark point.
    if( $this->performance and !$this->indexed ) Performance\Performance::point('Indexing pattern data...');
    
    // Get all pattern files.
    $patterns = array_map('Index::scan', PATTERN_GROUPS);
    
    // Return the files with their contents.
    if( $flag & Index::INDEX_READ ) {
      
      // Read all pattern files.
      $patterns = array_map('Index::read', $patterns);
    
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
      // Return all files with their contents.
      return $patterns;
      
    }
    
    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {
      
      // Get metadata for all pattern files.
      $patterns = array_map('Index::metadata', $patterns);
    
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
      // Return all files with their metadata.
      return $patterns;
      
    }
    
    // Otherwise, return only the file listing.
    else {
      
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
      
      // Return the file listing.
      return $patterns;
      
    }
    
  }
  
  // Convert pattern data into partial data.
  protected function getPartialData( array $patterns ) {
    
    // Get all patterns without their groupings.
    $patterns = array_merge(...array_values($patterns));
    
    // Convert the patterns to partials, and return the partials.
    return array_reduce($patterns, function($result, $pattern) {
      
      // Save the pattern's partial by its PLID.
      $result[$pattern->plid] = $pattern->pattern;

      // Alias the pattern's partial by its ID and include path.
      $result[$pattern->id] = &$result[$pattern->plid];
      $result[trim($pattern->path, '/')] = &$result[$pattern->plid];

      // Continue building partials.
      return $result;
      
    }, []);
    
  }
  
  // Locate all assets used by the site.
  protected function getAssetData( $flag = Index::INDEX_ONLY ) {
    
    // Add benchmark point.
    if( $this->performance and !$this->indexed ) Performance\Performance::point('Indexing asset data...');
    
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
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
      // Return all files with their contents.
      return $assets;
      
    }
    
    // Otherwise, return only the files with their metadata.
    else if( $flag & self::INDEX_METADATA ) {
      
      // Get metadata for all site data files.
      $assets = Index::metadata($assets);
    
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
      // Return all files with their metadata.
      return $assets;
      
    }
    
    // Otherwise, return only the file listing.
    else {
      
      // Add benchmark point.
      if( $this->performance and !$this->indexed ) Performance\Performance::finish();
      
      // Return the file listing.
      return $assets;
      
    }
    
  }
  
  // Identifies all known routes within a site from a precompiled set of indices.
  protected function getRouteData( array $site, array $assets = [] ) {
    
    // Add benchmark point.
    if( $this->performance and !$this->indexed ) Performance\Performance::point('Indexing route data...');
    
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
    
    // Get site routes.
    $site = $route($site['site']);
    
    // Get asset routes.
    $assets = $route($assets);
    
    // Also, get any preconfigured routes found within the templating engine itself.
    $engine = $route(array_get(CONFIG['config'], 'routes', []));
    
    // Merge site, asset, and engine routes.
    $routes = array_merge($engine, $site, $assets);
    
    // Filter out routes that are not for error pages, then get the existing error page codes from their route IDs.
    $codes = array_map(function($route) {
      
      // Get the route ID of the error page.
      return (int) $route->id;
      
    }, array_values(array_filter($routes, function($route) {
      
      // Filter out non-error pages.
      return $route->error;
      
    })));
    
    // Lastly, simulate routes for any error pages that have been defined via configurations but not identified elsewhere.
    $errors = array_map_use_both(function($data, $code) {
      
      // Simulate a route.
      return new Route([
        'endpoint' => "/$code",
        'id' => (string) $code
      ]);
      
    }, array_filter(CONFIG['errors'], function($code) use ($codes) {
      
      // Filter out any errors that have already been identified as routes.
      return !in_array($code, $codes);
      
    }, ARRAY_FILTER_USE_KEY));
    
    // Add benchmark point.
    if( $this->performance and !$this->indexed ) Performance\Performance::finish();
    
    // Merge the error routes, and return all routes.
    return array_merge($routes, $errors);
    
  }
  
  // Transforms all index data into actual endpoint data.
  protected function getEndpointData( array $environment, array $site, array $patterns, array $routes ) {
    
    // Initialize endpoints.
    $endpoints = [];
    
    // Initialize a helper method for building endpoints.
    $endpoint = function( Route $route ) use ($environment, $site, $patterns) {
      
      // For asset routes, the endpoint won't have any data or a template.
      if( $route->asset ) {
        
        // Initialize empty data and pattern.
        $data = null;
        $pattern = null;
        
      }
      
      // Otherwise, for non-asset routes, get the endpoint's data and template.
      else {
        
        // For redirecting endpoints, the endpoint won't have any data.
        if( isset($route->redirect) ) $data = null;
        
        // Otherwise, for non-redirecting endpoints, attempt to get the its data.
        else {
          
          // Find the endpoint's data.
          $data = isset($site['site'][$route->path]) ? $site['site'][$route->path] : null;
          
          // For error endpoints without data, use the error data within configurations.
          if( $route->error and !isset($data) ) $data = new Data([
            'data' => array_merge(CONFIG['errors'][(int) $route->id], ['code' => (int) $route->id])
          ]);
          
          // Otherwise, for non-error endpoints without data, simulate some data.
          else if( !isset($data) ) $data = new Data([]);
          
          // If the endpoint redirects, then clear the data.
          if( isset($data->data['redirect']) ) $data = null;
          
          // Otherwise, compile the data for the endpoint.
          else $data->data = self::compile($environment, $site, $data->data);
          
        }
        
        // For redirecting endpoints, the endpoint won't have a template pattern.
        if( is_null($data) ) $pattern = null;
        
        // Otherwise, for non-redirecting endpoints, attempt to get the its template pattern.
        else {

          // Get the endpoint's page type, if given.
          $pageType = array_get($data->data, 'template', false);

          // Lookup the endpoint's template pattern by page type when given.
          if( $pageType ) {

            // Get the endpoint's template pattern.
            $pattern = array_get(array_values(array_filter($patterns['templates'], function($pattern) use ($pageType) {

              // Find the template with the matching page type, PLID, or ID.
              return ($pattern->pageType == $pageType or $pattern->plid == $pageType or $pattern->id == $pageType);

            })), 0);

          }

          // Otherwise, for error endpoints, use the default error template.
          else if( $route->error ) $pattern = new Pattern([
            'template' => true,
            'pattern' => CONFIG['defaults']['errorTemplate']
          ]);

          // Otherwise, for non-error endpoints, assume that no template is available.
          else $pattern = null;
          
        }
        
      }

      // Convert the route, data, and template to an endpoint, and return it.
      return new Endpoint($route, $data, $pattern);
      
    };
    
    // Convert each route into an endpoint.
    foreach( $routes as $route ) { $endpoints[] = $endpoint($route); }
    
    // Return the endpoints.
    return $endpoints;
    
  }
  
  // Get all handlebars helpers.
  protected function getHelperData() {
    
    return (include ENGINE_ROOT.'/php/helpers/index.php')();
    
  }
  
}

?>