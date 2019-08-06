<?php

// Use dependencies.
use Moment\Moment;
use Moment\CustomFormats\MomentJs;
use Index\API;
use Index\Options;
use Performance\Performance;

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

  // The index of all partials.
  public $partials = [];

  // The index of all recognized endpoints.
  public $endpoints = [];

  // The index of all available handlebars helpers.
  public $helpers = [];

  // The paths used to save the index data.
  public static $paths = [
    'environment' => CONFIG['engine']['cache']['index'].'/environment/{group}/{src}.php',
    'site' => CONFIG['engine']['cache']['index'].'/site/{group}/{src}.php',

    'patterns' => CONFIG['engine']['cache']['index'].'/patterns/{group}/{plid}.php',
    'assets' => CONFIG['engine']['cache']['index'].'/assets/{endpoint}.php',
    'endpoints' => CONFIG['engine']['cache']['index'].'/endpoints/{endpoint}.php',
    'routes' => CONFIG['engine']['cache']['index'].'/routes/{endpoint}.php',

    'partials' => CONFIG['engine']['cache']['index'].'/partials.php',
    'helpers' => CONFIG['engine']['cache']['index'].'/helpers.php',

    'processes' => CONFIG['engine']['cache']['index'].'/processes.php',
    'queue' => CONFIG['engine']['cache']['index'].'/queue.php',
    'callbacks' => CONFIG['engine']['cache']['index'].'/callbacks.php',
    'lock' => CONFIG['engine']['cache']['index'].'/indexing.lock',
  ];

  // Capture output.
  public static $output = [];

  // Indicates when the initial indexing process has completed.
  public static $indexed = false;

  // The process ID of the current indexing instance.
  public static $pid = null;

  // The amount of time given before a lock expires in seconds.
  public static $expires = 300;

  // Defines flags for indexing modes.
  const INDEX_ONLY = 1;
  const INDEX_METADATA = 2;
  const INDEX_READ = 4;
  const INDEX_CLASS = 8;

  // Defines flags that can be used for the merge method.
  const MERGE_PATHS = 1;
  const MERGE_CONTENTS = 2;
  const MERGE_NORMAL = 4;
  const MERGE_RECURSIVE = 8;
  const MERGE_KEYED = 16;
  const MERGE_GROUPED = 32;
  const MERGE_OVERRIDE = 64;

  // Constructs the index.
  function __construct( Options $options ) {

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
      API::done(202, "The indexing process is currently active for '".DOMAIN."'. Your request was added to a queue.");

    }

     // Add benchmark point.
    if( BENCHMARKING ) Performance::point('Index', true);

    // Lock the indexing process.
    self::lock(true);

    // Reset the index flag.
    self::$indexed = false;

    // Reset the output.
    self::$output = [];

    // Set a anchor point where reindexing should start if and when triggered.
    reindex:

    // Wipe the queue.
    self::queue(false);

    // Get an index of all environment-wide data files, and cache it.
    $this->environment = [
      'metadata' => ($environment = self::getEnvironmentData(self::INDEX_METADATA)),
      'data' => self::classify($environment, 'Data')
    ];

    // Get an index of all site-wide data files, and cache it.
    $this->site = [
      'metadata' => ($site = self::getSiteData(self::INDEX_METADATA)),
      'data' => self::classify($site, 'Data')
    ];

    // Get an index of all patterns, and cache it.
    $this->patterns = [
      'metadata' => ($patterns = self::getPatternData(self::INDEX_METADATA)),
      'data' => self::classify($patterns, 'Pattern')
    ];

    // Build partials based on the pattern index.
    $this->partials = [
      'metadata' => [],
      'data' => ($partials = self::getPartialData($this->patterns['data']))
    ];

    // Get an index of all assets, and cache it.
    $this->assets = [
      'metadata' => ($assets = self::getAssetData(self::INDEX_METADATA)),
      'data' => array_combine(array_keys($assets), Index::classify(array_keys($assets), 'Asset'))
    ];

    // Get routes from the site and asset indices, and cache it.
    $this->routes = [
      'metadata' => [],
      'data' => ($routes = self::getRouteData($site, $assets))
    ];

    // Build endpoints based on the environment, site, pattern, and route indices.
    $this->endpoints = [
      'metadata' => [],
      'data' => ($endpoints = self::getEndpointData($this->environment['data'], $this->site['data'], $this->patterns['data'], $routes))
    ];

    // Get an index of handlebars helpers, and cache it.
    $this->helpers = [
      'metadata' => [],
      'data' => ($helpers = self::getHelperData())
    ];

    // Mutate the endpoint data.
    $this->endpoints['data'] = static::mutate($this->endpoints['data']);

    // Cache everything.
    self::cache('environment', $this->environment);
    self::cache('site', $this->site);
    self::cache('patterns', $this->patterns);
    self::cache('partials', $this->partials);
    self::cache('assets', $this->assets);
    self::cache('routes', $this->routes);
    self::cache('endpoints', $this->endpoints);
    self::cache('helpers', $this->helpers);

    // Set the indexed flag.
    self::$indexed = true;

    // If additional indexing requests were received while the last process was running, then reindex everything.
    if( !empty(self::queue()) ) goto reindex;

    // Fire any callbacks if given, and save the output.
    self::runCallback($options->callback);

    // Unlock the indexing process.
    self::lock(false);

    // Remove the process from the process log.
    self::log(false, self::$pid);

    // Add benchmark point.
    if( BENCHMARKING ) Performance::finish('Index');

  }

  // Call methods as static functions.
  public static function __callStatic( $method, $arguments ) {

    // Make some protected methods public.
    switch($method) {

      // Get partial index data.
      case 'getPartialData':

        // Get partial data.
        return static::getPartialData();

      // Get helper index data.
      case 'getHelperData':

        // Get helper data.
        return static::getHelperData();

      // Get asset index data.
      case 'getAssetData':

        // Get asset data.
        return static::getAssetData(static::INDEX_CLASS, 'Pattern');

      // Get endpoint index data.
      case 'getEndpointData':

        // Get endpoint data.
        return static::getEndpointData(static::INDEX_CLASS, 'Data');

      // Get index data for an asset endpoint.
      case 'getAssetEndpointData':

        // Get endpoint data.
        $endpoints = static::getEndpointData(static::INDEX_CLASS, 'Data');

        // Get asset endpoint data.
        return array_values(array_filter($endpoints, function($endpoint) {

          // Locate all asset endpoints.
          return $endpoint->asset;

        }));

      // Get index data for an asset endpoint.
      case 'getAssetEndpoint':

        // Get endpoint data.
        $endpoints = static::getEndpointData(static::INDEX_CLASS, 'Data');

        // Return the asset endpoint.
        return array_get(array_values(array_filter($endpoints, function($endpoint) use ($arguments) {

          // Find asset endpoints.
          if( !$endpoint->asset ) return false;

          // Find the endpoint data for the given endpoint path.
          return (is_array($endpoint->endpoint) ? in_array($arguments[0], $endpoint->endpoint) : $endpoint->endpoint == $arguments[0]);

        })), 0);

      // Get the indexer's lock status.
      case 'getLockStatus':

        // Get status.
        return static::lock();

      // Get the site's metadata.
      case 'getMetaData':

        // Get environment, site, and endpoint data.
        $environment = static::getEnvironmentData(static::INDEX_CLASS, 'Data');
        $sites = Index::getSiteData(Index::INDEX_CLASS, 'Data');
        $endpoint =   (isset($arguments[0]) and is_array($arguments[0])) ? $arguments[0] : [];

        // Return the site's metadata.
        return static::compile($environment, $sites, $endpoint);

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

  // Classify one or more set of files.
  protected static function classify( $files, $class = null ) {

    // Classify a single file.
    if( is_string($files) ) return self::read($files, $class);

    // Otherwise, classify an array of files.
    return array_map(function($files) use ($class) {

      // Read all site data files.
      return self::read($files, $class);

    }, $files);

  }

  // Mutate one or more files.
  protected static function mutate( $files ) {

    // Get a list of page types with their respective template IDs.
    $types = array_flip(CONFIG['config']['template']);

    // Initialize a helper method for mutating some endpoint data.
    $mutate = function( Endpoint $endpoint ) use ($types) {

      // Get the endpoint's data.
      $data = &$endpoint->data;

      // Only mutate endpoint data that exists.
      if( isset($data) ) {

        // Get the pattern's ID.
        $id = array_get($types, array_get($data->data, '@attributes.definition-path'));

        // Mutate the contents based on its ID.
        if( isset($id) ) $data->data = Mutator::mutate($data->data, $id);

      }

      // Return the endpoint with its mutated or unmutated data.
      return $endpoint;

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
  protected static function compile( array &$environment, array &$site, array &$endpoint = [] ) {

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
  protected static function cache( string $name, array &$index ) {

    // Set created and modified times to add into the cache data.
    $modified = $created = new DateTime();

    // Get the index's metadata and data.
    $metadata = &$index['metadata'];
    $data = &$index['data'];

    // Get the base file names.
    $phpFilenameBase = basename(self::$paths[$name]);
    $jsonFilenameBase = strtr($phpFilenameBase, ['.php' => '.json']);

    // Get the base file paths.
    $phpDestBase = self::$paths[$name];
    $jsonDestBase = strtr($phpDestBase, ['.php' => '.json']);

    // Detect the fields that should be bound within the filename.
    preg_match_all('/\{([\S]+?)\}/', $phpFilenameBase, $bindings, PREG_SET_ORDER);

    // Initialize a helper method for caching something.
    $cache = function( &$itemData, &$itemMetadata, array $bind = [] ) use (
      $name,
      $bindings,
      $phpFilenameBase,
      $jsonFilenameBase,
      $phpDestBase,
      $jsonDestBase,
      $created,
      $modified
    ) {

      // Build the item's cacheable data.
      $itemCacheable = [
        'data' => $itemData,
        'metadata' => $itemMetadata,
        'created' => $created,
        'modified' => $modified
      ];

      // Filter binding data to remove any bind data that was passed in.
      if( !empty($bind) ) $bindings = array_filter($bindings, function($binding) use ($bind) {

        // Only keep binding data that has not already been bound.
        return !in_array($binding[0], array_keys($bind));

      });

      // Get the values that should be bound within the item's filename.
      foreach( $bindings as $binding ) {

        // Get the key that should be bound.
        $key = $binding[1];

        // Bind the item's value that should be bound.
        $value = is_array($itemData) ? $itemData[$key] : $itemData->{$key};

        // Make sure the item's bound data is not an array.
        if( is_array($value) ) $value = array_last($value);

        // Save the value to be bound.
        $bind[$binding[0]] = $value;

      }

      // Get the item's file names.
      $phpFilename = strtr($phpFilenameBase, $bind);
      $jsonFilename = strtr($jsonFilenameBase, $bind);

      // Get the item's file paths.
      $phpDest = strtr($phpDestBase, $bind);
      $jsonDest = strtr($jsonDestBase, $bind);

      // Convert the item's cacheable data to a PHP string and JSON string.
      $php = '<?php return '.var_export($itemCacheable, true).'; ?>';
      $json = json_encode($itemCacheable, JSON_PRETTY_PRINT);

      // Try to save the index as a temporary file, and make sure no errors were thrown.
      try {

        // Create the temporary file.
        $phpTmp = Cache::tmp($php, "$name/$phpFilename", 0777);
        $jsonTmp = Cache::tmp($json, "$name/$jsonFilename", 0777);

        // Move the temporary file to the index, and overwrite any existing index file that's there.
        $phpTmp['move']($phpDest);
        $jsonTmp['move']($jsonDest);

      }

      // Otherwise, log that an error occurred when attempting to cache the index.
      catch( Exception $e ) {

        // Get the problematic item's file ID.
        $id = strtr($phpFilename, ['.php' => '']);

        // Log the error.
        error_log("Something went wrong while trying to create the index '$name' for '$id'.");

      }

      // Wipe the item's index data after its been cached in order to free up some memory.
      $itemData = null;
      $itemMetadata = null;
      $itemCacheable = null;

      // Then, unset the item's index data.
      unset($itemData);
      unset($itemMetadata);
      unset($itemCacheable);

    };

    // Cache the index data by type.
    switch($name) {

      // Cache environment, site, and/or patterns index data.
      case 'environment':
      case 'site':

        // Loop through the index groups.
        foreach( $data as $groupKey => &$groupData ) {

          // Cache each item within the group individually.
          foreach( $groupData as $itemFile => &$itemData ) {

            // Capture the item's metadata, if any.
            $itemMetadata = &$metadata[$groupKey][$itemFile] ?? [];

            // Get the source of the item.
            $src = strtr(File::source($itemFile), [
              '/_meta' => '',
              '/_global' => '',
              '/_shared' => ''
            ]);

            // Cache the item.
            $cache($itemData, $itemMetadata, [
              '{group}' => $groupKey,
              '{src}' => $src
            ]);

          }

          // Set the group's index data to null to free up some memory.
          $groupData = null;

          // Then, unset the group's index data.
          unset($groupData);

        }

        // Done.
        break;

      // Otherwise, cache patterns index data.
      case 'patterns':

        // Loop through the index groups.
        foreach( $data as $groupKey => &$groupData ) {

          // Cache each item within the group individually.
          foreach( $groupData as $itemKey => &$itemData ) {

            // Capture the item's metadata, if any.
            $itemMetadata = &$metadata[$groupKey][$itemKey] ?? [];

            // Cache the item.
            $cache($itemData, $itemMetadata, ['{group}' => $groupKey]);

          }

          // Set the group's index data to null to free up some memory.
          $groupData = null;

          // Then, unset the group's index data.
          unset($groupData);

        }

        // Done.
        break;

      // Otherwise, cache helpers and partials index data.
      case 'helpers':
      case 'partials':

        // Cache the data as is.
        $cache($data, $metadata);

        // Done.
        break;

      // Otherwise, cache all other index data.
      default:


        // Cache each item within the data set.
        foreach( $data as $itemKey => &$itemData ) {

          // Capture the item's metadata, if any.
          $itemMetadata = &$metadata[$itemKey] ?? [];

          // Cache the item.
          $cache($itemData, $itemMetadata);

        }

    }

  }

  // Keep a log of all active indexing processes.
  protected static function log( bool $state = null, $pid = null ) {

    // Get the file names.
    $phpFilename = basename(self::$paths['processes']);
    $jsonFilename = strtr($phpFilename, ['.php' => '.json']);

    // Get the index file paths.
    $phpDest = self::$paths['processes'];
    $jsonDest = strtr($phpDest, ['.php' => '.json']);

    // If a state was given, then log the process with the given state.
    if( isset($state) ) {

      // Get the current list of running processes if available, or initialize an empty list otherwise.
      $processes = Cache::exists($phpDest) ? Cache::include($phpDest) : [];

      // Add the PID to the process list if the state indicates that it should be added.
      if( $state === true and isset($pid) and !in_array($pid, $processes) ) $processes[] = $pid;

      // Otherwise, remove the PID from the process list.
      else if( $state === false and isset($pid) and in_array($pid, $processes) ) {

        // Get the index of the PID.
        $index = array_search($pid, $processes);

        // Set the PID to null to free up some memory.
        $processes[$index] = null;

        // Then, unset the PID.
        unset($processes[$index]);

      }

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

  // Keep a log of the active callbacks.
  public static function callback( string $id, string $pid = null, bool $state = null ) {

    // Get the file names.
    $phpFilename = basename(self::$paths['callbacks']);
    $jsonFilename = strtr($phpFilename, ['.php' => '.json']);

    // Get the index file paths.
    $phpDest = self::$paths['callbacks'];
    $jsonDest = strtr($phpDest, ['.php' => '.json']);

    // Get the current list of running callbacks if available, or initialize an empty list otherwise.
    $callbacks = Cache::exists($phpDest) ? Cache::include($phpDest) : [];

    // If a state was given, then log the callback with the given state.
    if( isset($state) ) {

      // Add the PID to the callback list if the state indicates that it should be added.
      if( $state === true ) $callbacks[$id] = $pid;

      // Otherwise, remove the PID from the callback list.
      else {

        // Set the PID to null to free up some memory.
        $callbacks[$id] = null;

        // Then, unset the PID.
        unset($callbacks[$id]);

      }

      // Convert the callbacks to a PHP string and JSON string.
      $php = '<?php return '.var_export($callbacks, true).'; ?>';
      $json = json_encode($callbacks, JSON_PRETTY_PRINT);

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
        error_log("Something went wrong while trying to log the callback.");

      }

    }

    // Otherwise, return the active process' PID if available or false for unregistered callbacks.
    return ($callbacks[$id] ?? false);

  }

  // Set or get the lock state of the indexing process.
  protected static function lock( bool $state = null ) {

    // Get the lock file path.
    $path = self::$paths['lock'];

    // Get the lock status.
    $status = Cache::exists($path);

    // If a lock file exists, then determine if it is invalid and/or expired, and adjust the lock status accordingly.
    if( $status ) {

      // Get the owner of the cache file and a list of all running processes.
      $owner = Cache::read($path);
      $processes = self::log();

      // If the process list is empty or the owner cannot be found within the process list, then assume the lock file is invalid.
      if( empty($processes) or !in_array($owner, $processes) ) {

        // Unlock the indexing process.
        Cache::delete($path);

        // Update the lock status.
        $status = false;

      }

      // Otherwise, determine if the lock file has expired.
      else {

        // Otherwise, get the expiry time of the lock file.
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

    // Get the file names.
    $phpFilename = basename(self::$paths['queue']);
    $jsonFilename = strtr($phpFilename, ['.php' => '.json']);

    // Get the index file paths.
    $phpDest = self::$paths['queue'];
    $jsonDest = strtr($phpDest, ['.php' => '.json']);

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
  protected static function getEnvironmentData( $flag = Index::INDEX_ONLY, string $class = null ) {

    // Add benchmark point.
    if( BENCHMARKING and !Index::$indexed ) Performance::point('Indexing environment data...');

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

    // Return the files with their contents loaded into the given class.
    if( $flag & Index::INDEX_CLASS ) {

      // Get metadata for all site data files.
      $environment = array_map('Index::metadata', $environment);

      // Instatiate an instance of the given class for each file.
      $environment = Index::classify($environment, $class);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their class instances.
      return $environment;

    }

    // Return the files with their contents.
    else if( $flag & Index::INDEX_READ ) {

      // Read all environment data files.
      $environment = array_map('Index::read', $environment);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their contents.
      return $environment;

    }

    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {

      // Get metadata for all environment data files.
      $environment = array_map('Index::metadata', $environment);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their metadata.
      return $environment;

    }

    // Otherwise, return only the file listing.
    else {

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return the file listing.
      return $environment;

    }

  }

  // Locate site-specific data files.
  protected static function getSiteData( $flag = Index::INDEX_ONLY, string $class = null ) {

    // Add benchmark point.
    if( BENCHMARKING and !Index::$indexed ) Performance::point('Indexing site data...');

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

    // Return the files with their contents loaded into the given class.
    if( $flag & Index::INDEX_CLASS ) {

      // Get metadata for all site data files.
      $site = array_map('Index::metadata', $site);

      // Instatiate an instance of the given class for each file.
      $site = Index::classify($site, $class);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their class instances.
      return $site;

    }

    // Return the files with their contents.
    else if( $flag & Index::INDEX_READ ) {

      // Get a list of page types with their respective template IDs.
      $types = array_flip(CONFIG['config']['template']);

      // Read all site data files.
      $site = array_map('Index::read', $site);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their contents.
      return $site;

    }

    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {

      // Get metadata for all site data files.
      $site = array_map('Index::metadata', $site);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their metadata.
      return $site;

    }

    // Otherwise, return only the file listing.
    else {

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return the file listing.
      return $site;

    }

  }

  // Locate all patterns.
  protected static function getPatternData( $flag = Index::INDEX_ONLY, string $class = null ) {

    // Add benchmark point.
    if( BENCHMARKING and !Index::$indexed ) Performance::point('Indexing pattern data...');

    // Get all pattern files.
    $patterns = array_map('Index::scan', PATTERN_GROUPS);

    // Return the files with their contents loaded into the given class.
    if( $flag & Index::INDEX_CLASS ) {

      // Get metadata for all site data files.
      $patterns = array_map('Index::metadata', $patterns);

      // Instatiate an instance of the given class for each file.
      $patterns = Index::classify($patterns, $class);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their class instances.
      return $patterns;

    }

    // Return the files with their contents.
    else if( $flag & Index::INDEX_READ ) {

      // Read all pattern files.
      $patterns = array_map('Index::read', $patterns);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their contents.
      return $patterns;

    }

    // Otherwise, return only the files with their metadata.
    else if( $flag & Index::INDEX_METADATA ) {

      // Get metadata for all pattern files.
      $patterns = array_map('Index::metadata', $patterns);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their metadata.
      return $patterns;

    }

    // Otherwise, return only the file listing.
    else {

      // Add benchmark point.
      if( $this->performance and !Index::$indexed ) Performance::finish();

      // Return the file listing.
      return $patterns;

    }

  }

  // Convert pattern data into partial data.
  protected static function getPartialData( array $patterns = null ) {

    // Get patterns if not given.
    $patterns = isset($patterns) ? $patterns : array_map(function($files) {

      // Read all pattern files.
      return self::read($files, 'Pattern');

    }, self::getPatternData(self::INDEX_METADATA));

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
  protected static function getAssetData( $flag = Index::INDEX_ONLY ) {

    // Add benchmark point.
    if( BENCHMARKING and !Index::$indexed ) Performance::point('Indexing asset data...');

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

    // Return the files with their contents loaded into the given class.
    if( $flag & Index::INDEX_CLASS ) {

      // Get metadata for all site data files.
      $assets = array_map('Index::metadata', $assets);

      // Instatiate an instance of the given class for each file.
      $assets = array_combine(array_keys($assets), Index::classify(array_keys($assets), $class));

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their class instances.
      return $assets;

    }

    // Return the files with their contents.
    else if( $flag & self::INDEX_READ ) {

      // Read all template data files.
      $assets = Index::read($assets);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their contents.
      return $assets;

    }

    // Otherwise, return only the files with their metadata.
    else if( $flag & self::INDEX_METADATA ) {

      // Get metadata for all site data files.
      $assets = Index::metadata($assets);

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return all files with their metadata.
      return $assets;

    }

    // Otherwise, return only the file listing.
    else {

      // Add benchmark point.
      if( BENCHMARKING and !Index::$indexed ) Performance::finish();

      // Return the file listing.
      return $assets;

    }

  }

  // Identifies all known routes within a site from a precompiled set of indices.
  protected static function getRouteData( array $site, array $assets = [] ) {

    // Add benchmark point.
    if( BENCHMARKING and !Index::$indexed ) Performance::point('Indexing route data...');

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
    if( BENCHMARKING and !Index::$indexed ) Performance::finish();

    // Merge the error routes, and return all routes.
    return array_merge($routes, $errors);

  }

  // Transforms all index data into actual endpoint data.
  protected static function getEndpointData( array $environment = null, array $site = null, array $patterns = null, array $routes = null ) {

    // Initialize arguments if not given.
    if( !isset($environment) ) $environment = Index::getEnvironmentData(Index::INDEX_CLASS, 'Data');
    if( !isset($site) ) $site = Index::getSiteData(Index::INDEX_CLASS, 'Data');
    if( !isset($patterns) ) $patterns = Index::getPatternData(Index::INDEX_CLASS, 'Pattern');
    if( !isset($routes) ) $routes = Index::getRouteData($site, Index::getAssetData(Index::INDEX_METADATA));

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

        // Otherwise, for non-redirecting endpoints, attempt to get its template pattern.
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

          // Otherwise, for dynamic endpoints, look for some template information.
          else if( $route->dynamic and isset($route->metadata['template']) ) $pattern = array_get(array_values(array_filter($patterns['templates'], function($pattern) use ($route) {

            // Find the template with the matching page type, PLID, or ID.
            return ($pattern->pageType == $route->metadata['template'] or $pattern->plid == $route->metadata['template'] or $pattern->id == $route->metadata['template']);

          })), 0);

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
  protected static function getHelperData() {

    // Load helpers.
    return (include ENGINE_ROOT.'/php/helpers/index.php')();

  }

  // Run a callback.
  protected static function runCallback( string $id = null ) {

    // Attempt to fire the callback if an ID was given.
    if( isset($id) and $id !== false ) {

      // Get the callback path.
      $path = CONFIG['engine']['callbacks']."/$id.php";

      // Look for the callback, and execute it if it exists.
      if( File::exists($path) ) {

        // Add benchmark point.
        if( BENCHMARKING ) Performance::point("Running $id callback...'");

        // Load the callback.
        $callback = new Callback($id, $path, ['PATHS' => array_map(function($path) {

          // Always use JSON paths.
          return strtr($path, ['.php' => '.json']);

        }, self::$paths)]);

        // Fire the callback, and get the PID if any.
        $pid = $callback->fire();

        // Save the PID if given.
        if( isset($pid) ) Index::$output[$id] = $pid;

        // Add benchmark point.
        if( BENCHMARKING ) Performance::finish();

      }

    }

  }

}

?>
