<?php

/*
 * Route
 *
 * Builds a set of routing data for an incoming request.
 * Routing data consists of information about where an
 * endpoint's files are expected to be found.
 */
class Route {
  
  // The endpoint of the incoming request.
  public $endpoint;
  
  // The anticpated data file location for the incoming request.
  public $data;
  
  // The global data file locations for the request.
  public $global = [];
  
  // The meta data file locations for the request.
  public $meta = [];
  
  // The shared data file locations for the request.
  public $shared = [];
  
  // The template file location for each template by ID.
  public $templates = [];
  
  // The anticipated cache file location for the incoming request.
  public $cache;
  
  // Indicates if the route is presumed to be an asset.
  public $asset = false;
  
  // Constructs the route.
  function __construct( Request $request ) {
    
    // Capture the endpoint of the incoming request.
    $this->endpoint = $endpoint = $request->endpoint;
   
    // Determine the data file's path and cache file path.
    $this->data = cleanpath(CONFIG['data']['site']['root']."/$endpoint");
    $this->cache = cleanpath(CONFIG['engine']['cache']['pages']."/$endpoint");
    
    // Get the requested endpoint's extension, if any.
    $ext = pathinfo($endpoint, PATHINFO_EXTENSION);
    
    // For asset files, account for the asset file's path potentially being aliased.
    if( isset($ext) and in_array($ext, array_keys(Mime::$mimes)) ) {
      
      // Set the asset flag.
      $this->asset = true;
      
      // Capture all potential data locations.
      $this->data = array_map(function($path) use ($endpoint) {
        
        // Get the potential asset path.
        return cleanpath("$path/$endpoint");
        
      }, CONFIG['assets']);
      
    }
    
    // For index files, account for the data file potentially not including the index keyword.
    if( substr($endpoint, -1) == '/' ) {
      
      // Add the index keyword onto the data file path and cache file path.
      $this->data .= 'index';
      $this->cache .= 'index';
      
    }
    
    // For data file paths, enable the use of any valid data file extensions.
    if( is_string($this->data) and (!isset($ext) or $ext === '') ) {
      
      // Get all valid data extension.
      $exts = array_merge(...array_values(Transformer::$transformers));
      
      // Get all potential data file paths based on valid data file extensions.
      $this->data = array_map(function($ext) {
        
        // Add the extension to the data file.
        return $this->data.".$ext";
        
      }, $exts);
      
    }
    
    // Add the cache file extension.
    $this->cache .= CONFIG['ext']['cache'];
    
    // Get the global data folder paths, maintaining override order.
    $this->global[] = CONFIG['data']['environment']['global'];
    $this->global[] = CONFIG['data']['site']['global'];
    
    // Get the meta data folder paths, maintaining override order.
    $this->meta[] = CONFIG['engine']['meta'];
    $this->meta[] = CONFIG['data']['environment']['meta'];
    $this->meta[] = CONFIG['data']['site']['meta'];
    
    // Get the shared data folder paths, maintaining override order.
    $this->shared[] = CONFIG['data']['environment']['shared'];
    $this->shared = array_merge($this->shared, ...array_values(CONFIG['data']['shared']));
    $this->shared[] = CONFIG['data']['site']['shared'];
    
    // Get the template pattern directory.
    $templates = CONFIG['patterns']['groups']['templates'];
    
    // Get template file paths and map them to their respective IDs.
    // FIXME: This is scanning the template folder for for template files. If this is still a performance issue, and we end up needing to lessen and/or eliminate reliance on the file system, we'll want to readdress how template files are registered within the templating engine.
    $this->templates = array_reduce(scandir_clean($templates), function($result, $template) use ($templates) {
      
      // Get the template's path.
      $path = cleanpath("$templates/$template");
      
      // Get the template's PLID.
      $plid = Template::plid($path);
      
      // Save template paths under their respective PLIDs.
      return array_set($result, $plid, $path);
      
    }, []);
    
  }
  
}

?>