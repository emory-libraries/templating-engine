<?php

// Initialize  utility methods.
trait Data_Utilities {
  
  // Convert an endpoint to a potential set of file names.
  protected function __getDataFileNames( $endpoint ) {
    
    // Initialize the results.
    $filenames = [];
    
    // Interpret the file's basename.
    $basename = $endpoint == '/' ? '' : trim($endpoint, '/');

    // Build the results.
    foreach( $this->parsers as $exts ) {
      
      // Add an entry for each extension.
      foreach( $exts as $ext ) { $filenames[] = trim("{$basename}/index{$ext}", '/'); }
      
    }
    
    // Add in the base file into the results set.
    if( $basename !== '' ) {
 
      // Amend the results with the base file.
      foreach( array_reverse($this->parsers) as $exts ) {
      
        // Add an entry for each extension.
        foreach( array_reverse($exts) as $ext ) { 
          
          // Add entries with high precedence.
          array_unshift($filenames, "{$basename}{$ext}"); 
        
        }

      }
      
    }
    
    // Add in alternative home page files.
    if( $basename === '' ) {
      
      // Define home page aliases.
      $aliases = ["home"];
      
      // Add an entry for each alias.
      foreach( $aliases as $alias ) {
        
        // Add an entry for each extension.
        foreach( $this->parsers as $exts ) {
          
          // Loop through each extension.
          foreach( $exts as $ext ) {
            
            // Add the entry.
            $filenames[] = "{$alias}{$ext}";
            
          }
          
        }
        
      }
      
    }

    // Return the set of filenames.
    return $filenames;
    
  }
  
  // Get the real paths for a data file.
  protected function __getDataFilePaths( $endpoint ) {
    
    // Get the potential data file names.
    $filenames = $this->__getDataFileNames($endpoint);
    
    // Get the contents of the data folder.
    $contents = scandir_recursive(CONFIG['data']['site']['root']);

    // Filter out any global data.
    $contents = array_values(array_filter($contents, function($file) {
      
      return (strpos($file, 'global') === false);
      
    }));

    // Filter the data files for potential mataches with the filenames.
    $filtered = array_values(array_filter($contents, function($file) use ($filenames) {

      return in_array($file, $filenames);
      
    })); 
    
    // Initialize the sorted file set.
    $sorted = [];
    
    // Sort the filtered data files based on their order of precedence.
    foreach( $filtered as $file ) { $sorted[array_search($file, $filenames)] = $file; }
    
    // Return the base data file along with any other supplemental files.
    return [
      'base' => ($file = (count($sorted) > 0 ? array_values($sorted)[0] : false)),
      'other' => array_values(array_filter($contents, function($path) use ($file) {
          
        return $path !== $file;
        
      }))
    ];
    
  }
  
  // Read and parse the contents of a data file.
  protected function __readDataFile( $path ) {
    
    // Initialize the result.
    $result = [];
    
    // Get the data.
    $data = file_get_contents($path); 
    
    // Get the file's extension.
    $ext = pathinfo($path, PATHINFO_EXTENSION); 

    // Parse the data based on the file extension.
    foreach( $this->parsers as $parser => $exts ) {

      if( in_array(".{$ext}", $exts) ) $result = $this->{$parser}($data);
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Read and parse all data files within a directory.
  protected function __readDataDirectory( $path ) {
    
    // Initialize the result.
    $result = [];
    
    // Ensure that the path is a valid directory.
    if( file_exists($path) and is_dir($path) ) {
      
      // Get the contents of the directory.
      $files = scandir_clean($path);
      
      // Read and save the contents of each file within the directory.
      foreach( $files as $file ) { 
        
        // Get the file's extension.
        $ext = pathinfo($file, PATHINFO_EXTENSION);
      
        // Use the filename as an ID/slug.
        $id = strtoslug(basename($file, ".{$ext}"));
        
        // Read the file's contents.
        $contents = $this->__readDataFile(cleanpath("{$path}/{$file}"));
     
        // Save the file contents.
        $result[] = array_merge((isset($contents) ? $contents : []), ['id' => $id]);
        
      }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Generate an array key from a data file name.
  protected function __keygen( $filename ) {
    
    // Get the file's extension.
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    
    // Erase the file's extension from the file name.
    $key = str_replace(".{$ext}", '', $filename);
    
    // Trim any excess characters from the key.
    $key = trim($key, "/ ");
    
    // Replace any slashes with dots.
    $key = str_replace('/', '.', $key);
    
    // Return the key.
    return $key;
    
  }
  
}

// Initialize parser methods.
trait Data_Parsers {
  
  // Register parser methods with their recognized extensions in their order of precedence.
  protected $parsers = [
    '__parseJSON' => ['.json', '.js'],
    '__parseYAML' => ['.yaml', '.yml'],
    '__parseXML'  => ['.xml']
  ];
  
  // Initialize the JSON parser.
  protected function __parseJSON( $data ) { return json_decode($data, true); }
  
  // Initialize the YAML parser.
  protected function __parseYAML( $data ) { return Yaml::parse($data); }
  
  // Initialize the XML parser.
  protected function __parseXML( $data ) { 
    
    // Retrieve the XML data models.
    $model = json_decode(file_get_contents(ENGINE_ROOT."/config/xml.json"), true);
    
    // Determine the XML fields that contain HTML and should be escaped.
    $escape = (isset($model['config']) and isset($model['config']['html'])) ? $model['config']['html'] : [];

    // Convert the XML data to an array.
    $data = object_to_array((new XML($data, $escape))->xml);
   
    // Transform and return the data.
    return (new Transformer($data, $model, 'XML'))->getTransformation(['case' => 'strtocamel']);
  
  }
  
}

// Creates a `Data` class for reading data.
class Data {
  
  // Load traits.
  use Data_Utilities, Data_Parsers;
  
  // Capture the route.
  protected $route;
  
  // Capture data to be merged.
  protected $merge = [];
  
  // Determine if dynamic data exists.
  private $dynamic = false;
  
  // Capture data.
  protected $data = [];
  
  // Constructor
  function __construct( $route = null, $merge = [] ) {
    
    // Capture the route.
    $this->route = $route;
    
    // Determine if dynamic data exists.
    if( isset($this->route['dynamic']) and in_array($this->route['dynamic'], [true, false]) ) {
      
      // Capture the dynamic setting.
      $this->dynamic = $this->route['dynamic'];
      
    }
    
    // Capture any data that should be merged.
    $this->merge = $merge;
    
  }
  
  // Getter
  function __get( $key ) { return array_get($this->data, $key); }
  
  // Setter
  function __set( $key, $value ) { return $this->data[$key] = $value; }
  
  // Get global data.
  public function getGlobalData() {
    
    // Get the path to the global data folder.
    $path = CONFIG['data']['site']['global'];
    
    // Scan the contents of the global data folder.
    $globals = file_exists($path) ? scandir_recursive($path) : [];
    
    // Initialize a result.
    $result = [];
    
    // Load all the global data.
    foreach( $globals as $src ) {
      
      // Determine the global file path.
      $path = cleanpath(CONFIG['data']['site']['global']."/{$src}");
      
      // Read and save the data.
      $result = array_merge($result, $this->__readDataFile($path));
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Get query parameter data.
  public function getQueryData() { 
    
    return ((isset($_GET) and !empty($_GET)) ? (new Cast($_GET))->castAll() : []);
    
  }
  
  // Get all data.
  public function getAllData() {
    
    // Initialize the result.
    $result = [
      '__global__' => $this->getGlobalData(),
      '__params__' => $this->getQueryData()
    ];
    
    // Get the path to the data folder.
    $data = CONFIG['data']['site']['root']; 
    
    // Scan the contents of the data directory.
    $contents = file_exists($data) ? scandir_recursive($data) : [];
    
    // Filter out any global data.
    $contents = array_values(array_filter($contents, function ($file) {
      
      return (strpos($file, 'global') === false);
      
    }));
    
    // Read the contents of each data file.
    foreach( $contents as $file ) {
      
      // Generate a key from the file name and path.
      $key = $this->__keygen($file);
      
      // Get the path to the file.
      $path = cleanpath($data."/{$file}");
      
      // Read and save the file's data.
      $result[$key] = $this->__readDataFile($path);
      
    }
    
    // Expand the data array.
    $result = array_expand($result);
    
    // Return the result.
    return $result;
    
  }
  
  // Get dynamic route-specific data.
  public function getDynamicData( $route ) { 
    
    // Get the route's dynamic data.
    return (new DynamicData($route))->getData();
    
  }
  
  // Get route-specific data.
  protected function getRouteData( $route ) {
    
    // Initialize the result.
    $result = [];
      
    // Get the data file name.
    $filename = $this->__getDataFilePaths($route['path']); 

    // Get the data file path.
    $path = cleanpath(CONFIG['data']['site']['root']."/{$filename['base']}"); 

    // Verify that the file exists.
    if( file_exists($path) ) {

      // Save the path and the data.
      $result['__data__'] = [
        'path' => $path,
        'extension' => ($ext = pathinfo($path, PATHINFO_EXTENSION)),
        'filename' => basename($path),
        'basename' => basename($path, ".{$ext}")
      ];
      $result = array_merge($result, $this->__readDataFile($path));

    }
    
    // Return the result.
    return $result;
    
  }
  
  // Get data for a route.
  public function getData( $route = null, $merge = [] ) {
   
    // Set the route if not set.
    if( !isset($route) and isset($this->route) ) $route = $this->route;

    // Initialize the result, and merge any supplemental data.
    $result = array_merge([
      '__route__' => $route,
      '__global__' => $this->getGlobalData(),
      '__params__' => $this->getQueryData(),
      '__data__' => [
        'path' => null,
        'extension' => null,
        'filename' => null,
        'basename' => null
      ]
    ], $this->merge, $merge);
    
    // Verify that a route was set.
    if( isset($route) ) {
    
      // Get the route-specific data.
      $result = array_merge($result, $this->getRouteData($route));
      
      // Get any dynamic data for dynamic routes.
      if( $this->dynamic === true ) $result['data'] = $this->getDynamicData($route);
      
    }

    // Return the result.
    return $result;
    
  }
  
}

?>