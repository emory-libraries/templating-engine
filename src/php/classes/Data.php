<?php

// Initialize  utility methods.
trait Data_Utilities {
  
  // Convert an endpoint to a potential set of file names.
  private function __getDataFileNames( $endpoint ) {
    
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
  private function __getDataFilePaths( $endpoint ) {
    
    // Get the potential data file names.
    $filenames = $this->__getDataFileNames($endpoint);
    
    // Get the contents of the data folder.
    $contents = scandir_recursive($this->config->DATA);

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
  private function __readDataFile( $path ) {
    
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
  
  // Generate an array key from a data file name.
  private function __keygen( $filename ) {
    
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
  private $parsers = [
    '__parseJSON' => ['.json', '.js'],
    '__parseYAML' => ['.yaml', '.yml']
  ];
  
  // Initialize the JSON parser.
  private function __parseJSON( $data ) { return json_decode($data, true); }
  
  // Initialize the YAML parser.
  private function __parseYAML( $data ) { return Yaml::parse($data); }
  
}

// Creates a `Data` class for reading data.
class Data {
  
  // Load traits.
  use Data_Utilities, Data_Parsers;
  
  // Capture configurations.
  protected $config;
  
  // Capture the endpoint.
  private $endpoint;
  
  // Constructor
  function __construct( $endpoint = null ) {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations.
    $this->config = $config;
    
    // Capture the endpoint.
    $this->endpoint = $endpoint;
    
  }
  
  // Getter
  function __get( $key ) { return array_get($this->data, $key); }
  
  // Setter
  function __set( $key, $value ) { return $this->data[$key] = $value; }
  
  // Get global data.
  public function getGlobalData() {
    
    // Get the path to the global data folder.
    $path = $this->config->DATA_GLOBAL;
    
    // Scan the contents of the global data folder.
    $globals = file_exists($path) ? scandir_recursive($path) : [];
    
    // Initialize a result.
    $result = [];
    
    // Load all the global data.
    foreach( $globals as $src ) {
      
      // Determine the global file path.
      $path = cleanpath($this->config->DATA_GLOBAL."/{$src}");
      
      // Read and save the data.
      $result = array_merge($result, $this->__readDataFile($path));
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Get query parameter data.
  public function getQueryData() {
    
    return (new Cast(isset($_GET) and !empty($_GET) ? $_GET : []))->castAll();
    
  }
  
  // Get all data.
  public function getAllData() {
    
    // Initialize the result.
    $result = [
      '__global__' => $this->getGlobalData(),
      '__params__' => $this->getQueryData()
    ];
    
    // Get the path to the data folder.
    $data = $this->config->DATA;
    
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
  
  // Get data for an endpoint.
  public function getData( $endpoint = null ) {
    
    // Set the endpoint if not set.
    if( !isset($endpoint) and isset($this->endpoint) ) $endpoint = $this->endpoint;
    
    // Initialize the result.
    $result = [
      '__file__' => [
        'path' => null,
        'filename' => null,
        'extension' => null
      ],
      '__global__' => $this->getGlobalData(),
      '__params__' => $this->getQueryData()
    ];
    
    // Get endpoint data if available.
    if( isset($endpoint) ) {
    
      // Get the data file name.
      $filename = $this->__getDataFilePaths($endpoint);

      // Load data if available.
      if( isset($filename['base']) ) {

        // Get the data file path.
        $path = cleanpath($this->config->DATA."/{$filename['base']}");

        // Verify that the file exists.
        if( file_exists($path) ) {

          // Save the path and the data.
          $result['__file__'] = [
            'path' => $path,
            'filename' => basename($path),
            'extension' => pathinfo($path, PATHINFO_EXTENSION)
          ];
          $result = array_merge($result, $this->__readDataFile($path));

        }

      }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
}

?>