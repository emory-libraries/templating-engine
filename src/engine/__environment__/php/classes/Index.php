<?php

/*
 * Index Utilities
 *
 * Utility methods for the Index class.
 */
trait Index_Utilities {
  
  // Convert a list of files to an associative array using file names as IDs.
  private static function makeFilesAssociative( array $list, $nesting = '' ) {

    // Traverse the file list.
    foreach( $list as $source => $files ) {
      
      // Recursively convert nested arrays into associative arrays.
      if( is_array($files) and is_associative_array($files) ) {
        
        // Convert the nested array.
        $list[$source] = self::makeFilesAssociative($files, (isset($nesting) ? "$nesting.$source" : $source));
        
      }
      
      // Otherwise, convert simple (non-associative) arrays into associative arrays.
      else if( is_array($files) ) {
        
        // Convert the array.
        $list[$source] = array_reduce($files, function($result, $file) use ($source) {
          
          // Get the file endpoint as an array key.
          $key = str_replace('/', '.', trim(File::endpoint($file), '/'));
      
          // If the key is prefixed with the source, remove it.
          $key = preg_replace('/^'.kebabcase($source).'\./', '', $key);

          // Use the key to save the file.
          return array_set($result, $key, $file);
          
        }, []);
        
      }
      
      // Otherwise, assume the list was non-associative array and files is actually a path.
      else {
        
        // Get the file's key path within the array based on the file's endpoint.
        $key = str_replace('/', '.', trim(File::endpoint($files), '/'));
          
        // Unset the original index.
        unset($list[$source]);
        
        // Save the file under the new key.
        $list = array_set($list, $key, $files);
        
      }
      
    }
    
    // Return the associative file list.
    return $list;
    
  }
  
  // Read all files in a list of files and get their contents.
  private static function readFiles( array $list, $class ) {
    
    // Traverse the file list.
    foreach( $list as $id => $file ) {
      
      // Recursively read all files within nested arrays.
      if( is_array($file) ) $list[$id] = self::readFiles($file, $class);
      
      // Otherwise, read the file from its path.
      else $list[$id] = [
        'path' => $file,
        'data' => new $class($file)
      ];
      
    }
    
    // Return the files with their contents.
    return $list;
    
  }
  
  // Extract routes from an array of site data.
  private static function extractSiteRoutes( array $site ) {
    
    // Travese the site data.
    foreach( $site as $pageId => $pageData ) {
      
      // Extract routes when given simple page data consisting of only a page path.
      if( is_string($pageData) ) $site[$pageId] = new Route($pageData);
      
      // Extract routes when given complex page data consisting of the page path and data.
      else if( isset($pageData['path']) and isset($pageData['data']) ) {
        
        // Use the page path to get the route data.
        $site[$pageId] = new Route($pageData['path']);
        
      }
      
      // Otherwise, recursively extract routes from nested folders within the site.
      else if( is_array($pageData) ) $site[$pageId] = self::extractSiteRoutes($pageData);
      
    }
    
    // Return the site routes.
    return $site;
    
  }
  
}

/*
 * Index
 *
 * This indexes all of the site data and templates.
 */
class Index {
  
  // Load utility methods.
  use Index_Utilities;
  
  // The data found and made available to the site.
  public $data = [];
  
  // The known templates.
  public $templates = [];
  
  // The index of known routes within the site.
  public $routes = [];
  
  // Constructs the index.
  function __construct() {
    
    // Get an index of all the data.
    $this->data = [
      'environment' => self::getEnvironmentData(true),
      'site' => self::getSiteData(true)
    ];
    
    // Get an index of all the templates.
    $this->templates = self::getTemplates(true);
    
    // Get an index of all routes within the active site.
    $this->routes = self::getRoutes($this->data); 
    
    // Also, get preconfigured routes.
    $routes = array_reduce(array_map(function($route) {
      
      // Get the route's endpoint, and convert it to a route object.
      return [
        'endpoint' => $route['endpoint'],
        'route' => new Route($route)
      ];
      
    }, CONFIG['config']['router']), function($routes, $route) {
      
      // Merge all routes into an associative array.
      return array_set($routes, str_replace('/', '.', trim($route['endpoint'], '/')), $route['route']);
      
    }, []);
    
    // Merge preconfigured routes with existing routes.
    $this->routes = array_merge_exact_recursive($routes, $this->routes);
  
  }
  
  // Locate environment-specific data files.
  public static function getEnvironmentData( $read = false ) {
    
    // Get environment-specific data directories.
    $environment = [
      'meta' => array_get(CONFIG, 'data.environment.meta'),
      'global' => array_get(CONFIG, 'data.environment.global'),
      'shared' => array_get(CONFIG, 'data.environment.shared')
    ];
    
    // Get shared data files.
    $shared = array_get(CONFIG, 'data.shared');
    
    // Find data files.
    $environment = array_map(function($directory) {
      
      // Initialize the result.
      $files = [];
      
      // Verify that the directory exists, and scan it recursively for files.
      if( file_exists($directory) ) $files = scandir_recursive($directory, $directory);
       
      // Return the listing of data files.
      return $files;
      
    }, $environment);
    
    // Convert file lists to associative arrays.
    $environment = self::makeFilesAssociative($environment);
    $shared = self::makeFilesAssociative($shared);
    
    // Remove the shared key from site-specific shared file data because it's redundant.
    foreach( $shared as $site => $files ) { $shared[$site] = $files['shared']; }

    // Return only the files listing if the read flag is not set.
    if( !$read ) {
      
      // Merge shared data files with environment-specific shared files.
      $environment['shared'] = array_merge($environment['shared'], $shared);
      
      // Return environment-specific files.
      return $environment;
      
    }
    
    // Otherwise, read the contents of all files.
    $environment = self::readFiles($environment, 'Data');
    $shared = self::readFiles($shared, 'Data');
    
    // Merge the shared environment data.
    $environment['shared'] = array_merge($environment['shared'], $shared);
    
    // Return all files with their contents.
    return $environment;
    
  }
  
  // Locate site-specific data files.
  public static function getSiteData( $read = false ) {
    
    // Get site-specific data directories.
    $site = [
      'meta' => array_get(CONFIG, 'data.site.meta'),
      'global' => array_get(CONFIG, 'data.site.global'),
      'shared' => array_get(CONFIG, 'data.site.shared'),
      'site' => array_get(CONFIG, 'data.site.root')
    ];
    
    // Find data files.
    $site = array_map(function($directory) {
      
      // Initialize the result.
      $files = [];
      
      // Verify that the directory exists, and scan it recursively for files.
      if( file_exists($directory) ) $files = scandir_recursive($directory, $directory);
       
      // Return the listing of data files.
      return $files;
      
    }, $site);
    
    // Remove meta, global, and shared data from site data.
    $site['site'] = array_values(array_diff($site['site'], $site['meta'], $site['global'], $site['shared']));
    
    // Convert the file list to an associative array.
    $site = self::makeFilesAssociative($site);
    
    // Return only the files listing if the read flag is not set.
    if( !$read ) return $site;
    
    // Otherwise, get the contents of all files.
    $site = self::readFiles($site, 'Data');
    
    // Return all files with their contents.
    return $site;
    
  }
  
  // Locate template patterns.
  public static function getTemplates( $read = false ) {
    
    // Get template directory.
    $directory = array_get(CONFIG, 'patterns.groups.templates');
    
    // Find template files.
    $templates = array_map(function($template) use ($directory) {
      
      // Get the full template path.
      return cleanpath("$directory/$template");
      
    }, scandir_recursive($directory));
    
    // Convert the file list to an associative array.
    $templates = self::makeFilesAssociative($templates);
    
    // Remove the templates directory key from template file data because it's redundant.
    $templates = $templates[basename($directory)];
    
    // Return only the files listing if the read flag is not set.
    if( !$read ) return $templates;
    
    // Otherwise, read the contents of all files.
    $templates = self::readFiles($templates, 'Template');
    
    // Return all files with their contents.
    return $templates;
    
  }
  
  // Identifies all known routes within the active site using the data and templates indices.
  public static function getRoutes(  array $data ) {
    
    // Initialize the routes.
    $routes = [];
    
    // Get site-specific data.
    $site = array_get($data, 'site.site', []);
    
    // Convert site data to routes.
    $routes = self::extractSiteRoutes($site);
    
    // Return the routes.
    return $routes;
    
  }
  
  // Compiles an array of global data, where site-level globals may overwrite environment-level globals.
  public function getGlobalData() {
    
    // Extract environment-level global data.
    $environment = array_map(function($data) {

      // Extract the data.
      return isset($data['data']) ? $data['data']->data : [];

    }, array_get($this->data, 'environment.global', []));
    
    // Extract site-level global data.
    $site = array_map(function($data) {

      // Extract the data.
      return isset($data['data']) ? $data['data']->data : [];

    }, array_get($this->data, 'site.global', []));
    
    // Recursively merge the site data into the environment data.
    return array_merge_recursive($environment, $site);
    
  }
  
  // Compiles an array of meta data, where site-level metas may overwrite environment-level metas.
  public function getMetaData() {
    
    // Extract environment-level meta data.
    $environment = array_map(function($data) {

      // Extract the data.
      return isset($data['data']) ? $data['data']->data : [];

    }, array_get($this->data, 'environment.meta', []));
    
    // Extract site-level meta data.
    $site = array_map(function($data) {

      // Extract the data.
      return isset($data['data']) ? $data['data']->data : [];

    }, array_get($this->data, 'site.meta', []));
    
    // Recursively merge the site data into the environment data.
    return array_merge_recursive($environment, $site);
    
  }
  
  // Compiles an array of cross-site shared data, where data from each site is are grouped by file ID.
  public function getSharedData() {
    
    // Recursively merge and group the shared site data into a single data set.
    return array_reduce(array_get($this->data, 'environment.shared', []), function($shared, $site) {
      
      // Merge each site's shared data into a single data set.
      foreach($site as $id => $data) {

        // Initialize the data set if it doesn't already exist.
        if( !isset($shared[$id]) ) $shared[$id] = [];

        // Merge the data into the data set.
        if( isset($data['data']) ) $shared[$id][] = $data['data']->data;

      }

      // Continue reducing.
      return $shared;

    }, []);
    
  }
  
  // Get the data by key or endpoint.
  public function getData( $key ) {
    
    // Lookup the data that correlates to the given endpoint, or return an empty data set if the endpoint was not found.
    return array_get($this->data, 'site.site'.str_replace('/', '.', $key));
    
  }
  
  // Extracts the data array for a given endpoint.
  public function getEndpointData( $endpoint ) {
    
    // Lookup the data that correlates to the given endpoint.
    $data = $this->getData($endpoint);
    
    // Return the data if it exists, or an empty data set otherwise.
    // FIXME: When no data is found for an endpoint, most likely because the endpoint doesn't exist, should we return an empty data set?
    return (isset($data) ? $data['data']->data : []);
    
  }
  
  // Get a template by ID or PLID.
  public function getTemplate( $id ) {
    
    // Lookup the template that has the given ID or PLID. 
    $template = array_values(array_filter(array_values($this->templates), function($template) use ($id) {

      // Get the template by either ID or PLID.
      return in_array($id, [$template['data']->id, $template['data']->plid]);
      
    }));
   
    // Return the template if it exists, or false otherwise.
    return (isset($template[0]) ? $template[0] : false);
    
  }
  
  // Get a route by endpoint.
  public function getRoute( $endpoint ) {
    
    // Get all routes.
    $routes = array_values(array_flatten($this->routes));
    
    // Find the route that uses the endpoint.
    $route = array_values(array_filter($routes, function($route) use ($endpoint) {
      
      // Determine if the routes endpoints match.
      return (is_array($route->endpoint) ? in_array($endpoint, $route->endpoint) : $route->endpoint == $endpoint);
      
    }));
    
    // Return the route, or false otherwise.
    return (isset($route[0]) ? $route[0] : false);
    
  }
  
  // Extracts the template contents for a given endpoint.
  public function getEndpointTemplate( $endpoint ) {
    
    // Find the route that uses the endpoint, or false otherwise.
    $route = $this->getRoute($endpoint);

    // If no route was found for the endpoint, then return an empty template.
    // FIXME: When no route is found for an endpoint, meaning the route doesn't exist, should we return an empty template?
    if( $route === false ) return ''; 
    
    // Lookup the template that the route uses.
    $template = $this->getTemplate($route->template); 
    
    // Get the default template.
    $default = $this->getTemplate(CONFIG['defaults']['template']); 
    
    // Return the template if it exists, or return the default template.
    // FIXME: When no template is found for a route, meaning the template hasn't been created or just doesn't exist, should we return the default template?
    return ($template !== false ? $template['data']->template : $default['data']->template);
    
  }
  
}

?>