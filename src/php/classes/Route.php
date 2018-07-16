<?php

// Use dependencies.
use Symfony\Component\Yaml\Yaml;

// Creates a `Route` class for managing endpoints.
class Route {
  
  // Load the router.
  private $router = [];
  
  // Capture configurations.
  protected $config;
  
  // Parse the endpoint.
  public $route = '';
  public $template = null;
  public $query = [];
  
  // Constructor
  function __construct( Config $config ) {
    
    // Save the configurations
    $this->config = $config;
    
    // Load the router.
    $this->router = $config->ROUTER;
    
    // Parse the endpoint.
    $endpoint = explode('?', str_replace($config->ROOT_PATH, '', $_SERVER['REQUEST_URI']), 2);
    
    // Save the endpoint data.
    $this->route = $endpoint[0] == '/' ? $endpoint[0] : rtrim($endpoint[0], '/');
    
    // Look for a set of possible routes.
    $route = array_values(array_filter($this->router, function($route) {
      
      return $route['path'] == $this->route;
      
    }));
    
    // Verify that a route exists.
    if( count($route) > 0 ) {
      
      // Get the template data.
      $this->template = $route[0]['template'];
      
    }

    // Get the query string parameters, and cast their data types.
    $query = new Cast($_GET);
    
    // Save the query string.
    $this->query = $query->castAll();
    
  }
  
  // Get data about a template path.
  private function aboutTemplate( $template ) {
    
    // Initialize the result.
    $result = [
      'template' => [
        'path' => null,
        'modified' => null
      ],
      'cache' => [
        'path' => null,
        'modified' => null,
        'active' => false
      ]
    ];
    
    // Extract extensions data.
    $ext = $this->config->EXT;
    
    // Extract root and cache paths.
    $root = $this->config->TEMPLATES;
    $cache = $this->config->CACHED_TEMPLATES;

    // Build paths.
    $result['template']['path'] = "{$root}/{$template}{$ext['template']}";
    $result['cache']['path'] = cleanpath($cache."/".dirname($template)."/".basename($template, $ext['template']).$ext['cache']);

    // Look for the template.
    if( !file_exists($result['template']['path']) ) return false;
    
    // Get the template data.
    $result['template']['modified'] = filemtime($result['template']['path']);
    
    // Check if the template was cached.
    if( file_exists($result['cache']['path']) ) {
      
      $result['cache']['modified'] = filemtime($result['cache']['path']);
      $result['cache']['active'] = true;
      
    }

    // Return the result.
    return $result;
    
  }
  
  // Retrieve the template(s) for the route.
  public function getTemplate() {
    
    // Treat arrays as an order of precedence.
    if( is_array($this->template) ) {
      
      // Loop through templates.
      foreach( $this->template as $id ) { 
        
        // Attempt to get some data about the template.
        if( ($data = $this->aboutTemplate($id)) !== false ) {
          
          // Save the template data.
          $template = $data;
          
          // Break after a template was found.
          break;
          
        }
        
      }
      
    }
    
    // Otherwise, look for the given template.
    else {
      
      // Save the template data if available.
      if( ($data = $this->aboutTemplate($this->template)) !== false ) $template = $data;
      
    }
    
    // Redirect to an error page if no template was loaded.
    if( !isset($template) ) {
      
      // Get the template name.
      $template = $this->aboutTemplate( 'error' );
      
      // Set error page code and message.
      $this->query['code'] = 400;
      $this->query['message'] = 'Not Found';
      
    } 
  
    // Return the template.
    return $template;
    
  }
  
  // Retrieve gloabl data if it exists.
  private function globalData() {
    
    // Get the path to the global data folder.
    $path = cleanpath($this->config->DATA."/_global");
    
    // Scan the contents of the global data folder.
    $globals = file_exists($path ) ? scandir_recursive($path) : [];
    
    // Initialize a result array.
    $data = [];
    
    // Load all the global data.
    foreach( $globals as $src ) {
      
      // Determine the global file path.
      $path = cleanpath($this->config->DATA."/_global/$src");
      
      // Read and save the data.
      $data = array_merge($data, $this->readData($path));
      
    }
    
    // Return the result.
    return $data;
    
  }
  
  // Find the corresponding data file.
  private function findData( $route ) {
    
    // Convert the route to a potential filename.
    $filename = $route == '/' ? '' : trim($route, '/');
    
    // Extract extensions data.
    $ext = $this->config->EXT;
    
    // Identify potential file matches in order of preference.
    $lookups = [
      trim("$filename/index{$ext['data']}", '/'),
      trim("$filename/home{$ext['data']}", '/')
    ]; 
    
    // Add the base filename.
    if( $filename !== '' ) array_unshift($lookups, "{$filename}{$ext['data']}");
    
    // Get the contents of the data folder.
    $contents = scandir_recursive($this->config->DATA); 
    
    // Ignore all global data.
    $contents = array_filter($contents, function($file) {
      
      return (strpos($file, 'global') === false);
      
    });
    
    // Filter the data files for potential matches.
    $filtered = array_values(array_filter($contents, function($file) use ($lookups) {

      return in_array($file, $lookups);
      
    })); 
    
    // Initialize resulting files.
    $files = [];
    
    // Sort the filtered data files.
    foreach( $filtered as $file ) { $files[array_search($file, $lookups)] = $file; }
    
    // Identify the base file and all other data files. 
    $result = [
      'base' => ($file = (count($files) > 0 ? array_values($files)[0] : false)),
      'other' => array_filter($contents, function($path) use ($file) {
        
          return $path !== $file;
        
      })
    ];

    // Return the data file.
    return $result;
    
  }
  
  // Read and parse a data file given its path.
  private function readData( $path ) {
    
    // Extract extension data.
    $ext = $this->config->EXT;
    
    // Get the data.
    $data = file_get_contents($path); 

    // Parse the data as JSON.
    if( in_array($ext['data'], ['.json', '.js']) ) $data = json_decode($data, true);

    // Or, parse the data as YAML.
    else if( in_array($ext['data'], ['.yml', '.yaml']) ) $data = Yaml::parse($data);
    
    // Return the parsed data.
    return $data;
    
  }
  
  // Retrieve the data for the route.
  public function getData() {
    
    // Initialize result.
    $result = [
      'path' => null,
      'data' => []
    ];
    
    // Get the data file name.
    $file = $this->findData($this->route);
    
    // Retrieve any global data.
    $global = $this->globalData();
    
    // Load any global data if available.
    if( isset($global) and !empty($global) ) {
      
      $result['data'] = array_merge($result['data'], $global);
      
    }
    
    // Load data if a valid file name is given.
    if( isset($file['base']) ) {
    
      // Get the data path.
      $result['path'] = cleanpath($this->config->DATA."/{$file['base']}"); 

      // Check if the data exists.
      if( file_exists($result['path']) ) {

        // Get the data.
        $result['data'] = array_merge($result['data'], $this->readData($result['path']));

      }
      
    }
    
    // Load all other data.
    if( isset($file['other']) and !empty($file['other']) ) {
      
      // Merge all other data as supplemental data.
      foreach( $file['other'] as $src ) {
        
        // Build the path.
        $path = cleanpath($this->config->DATA."/$src");
        
        // Check if the data exists.
        if( file_exists($path) ) {
          
          // Get the data.
          $result['data'][$src] = $this->readData($path);
          
        }
        
      }
      
    }
    
    // Merge any query data.
    $result['data'] = array_merge($result['data'], $this->query);

    // Return the result.
    return $result;
    
  }
  
}

?>