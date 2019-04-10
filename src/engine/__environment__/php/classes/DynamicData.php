<?php

// Initialize utility methods.
trait DynamicData_Utilities {
  
  // Define a regex for identifying dynamic values.
  protected $regex = '/^\:([a-z0-9-]+)$/i';
  
  // Generate a regex from a dynamic endpoint model.
  private function __endpointRegex( $model, $insensitive = true ) {
    
    // Get the path parts separately.
    $parts = explode('/', $model);
    
    // Build in flexibility for dynamic parts.
    $parts = array_map(function($part) {
      
      // Look for a dynamic value.
      if( preg_match($this->regex, $part, $id) ) {
        
        // Replace the value with a regular expression.
        return "(?<{$id[1]}>[a-z0-9-]+)";
        
      }
      
      // Otherwise, use the value as is.
      return $part;
        
    }, $parts);
    
    // Return the dynamic endpoint as a regex.
    return '/^'.implode('\/', $parts).'$/'.($insensitive ? 'i' : '');
    
  }
  
  // Get source data.
  private function __getSourceData( $path, array $sources ) { 
    
    // Initialize the result.
    $result = [];
    
    // Retrieve the contents for each source.
    foreach( $sources as $id => $source ) {

      // Get the source's path.
      $src = cleanpath("{$path}/{$source}");

      // Get the source data.
      $result[$id] = [
        'path' => $source,
        'data' => $this->__readDataDirectory($src)
      ];

    }
    
    // Return the result.
    return $result;
    
  }
  
  // Get endpoint data.
  private function __getEndpointData( $active, array $endpoints ) {
    
    // Initialize the result.
    $result = [];
    
    // Match active endpoint to its data model.
    foreach( $endpoints as $endpoint ) {
      
      // Convert the data model to a regex.
      $regex = $this->__endpointRegex($endpoint['endpoint']); 
      
      // Find the data model that the active endpoint uses.
      if( preg_match($regex, trim($active, '/ '), $dynamics) ) {
        
        // Remove the original value from our dynamic parameters.
        array_shift($dynamics);
        
        // Capture the data model and our matched parameters.
        $result = array_merge($endpoint, [
          'regex' => $regex,
          'params' => $dynamics
        ]);
          
        // Quit looking for other matches.
        break;
        
      }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Apply filter data.
  private function __applyFilters( array $data, array $filters, array $params = [] ) {
    
    // Make sure params is set.
    if( !isset($params) ) $params = [];
    
    // Determine when to return a single item or an item set.
    $single = false;
    
    // Apply filters sequentially.
    foreach( $filters as $key => $value ) {
      
      // Don't filter if any value is acceptable.
      if( $value === '*' ) continue;

      // For dynamic values, use the value of the field within the endpoint's parameters instead.
      if( preg_match($this->regex, $value, $field) ) {
        
        // Set the value equal to the parameter value.
        $value = $params[$field[1]];
        
        // Indicate that there should only be a single value returned.
        $single = true;
        
      }

      // Filter the data based on the value.
      $data = array_filter_key($key, $value, $data);

    }
    
    // Return the filtered data.
    return $single ? (empty($data) ? $data : $data[0]) : $data;
    
  }
  
  // Check if a route is a valid dynamic endpoint.
  private function __isValid( $route ) {
    
    // Get data about the route.
    $data = $this->getRouteData($route);
    
    // Make sure a source was given.
    if( !isset($data['source']) or empty($data['source']) ) return false;
    
    // Make sure that endpoints exists.
    if( !isset($data['endpoints']) or empty($data['endpoints']) ) return false;
    
    // Capture the endpoint.
    $endpoint = $this->__getEndpointData($route['endpoint'], $data['endpoints']);
    
    // Make sure that the endpoints is configured.
    if( !isset($endpoint) or empty($endpoint) ) return false;
    
    // Otherwise, assume that it's valid.
    return true;
    
  }
  
}

// Creates a `DynamicData` class for handling data for dynamic endpoints.
class DynamicData extends Data {
  
  // Load traits.
  use DynamicData_Utilities;
  
  // Constructor
  function __construct( $route = null, $merge = [] ) {
    
    // Construct the parent.
    parent::__construct($route, $merge);
    
  }
  
  // Get the dynamic route-specific data.
  protected function getDynamicRouteData( $route ) {
    
    // Get the default data.
    $default = $this->getRouteData($route);
    
    // Initialize the result.
    $result = [];
      
    // Get the dynamic data file directory.
    $directory = dirname(($this->__getDataFilePaths($route['path']))['base']); 

    // Get the dynamic data file path.
    $path = cleanpath("{$this->config->DATA}/{$directory}");
    
    // Ensure that the route exists as a valid dynamic endpoint 
    if( $this->__isValid($route) ) {
    
      // Get source data.
      $result['source'] = $this->__getSourceData($path, $default['source']);

      // Get endpoint data.
      $result['endpoint'] =  $this->__getEndpointData($route['endpoint'], $default['endpoints']);

      // Get the data to be filtered.
      $data = $result['source'][$result['endpoint']['source']]['data'];

      // Apply filters sequentially.
      $result = array_merge($result, $this->__applyFilters(
        $data, 
        $result['endpoint']['filter'],
        array_get($result['endpoint'], 'params')
      ));
      
      // Return the result.
      return $result;
      
    }
    
    // Otherwise, the page doesn't exists.
    return false;
    
  }
  
  // Get the dynamic data.
  public function getData( $route = null, $merge = [] ) {
    
    // Set the route if not set.
    if( !isset($route) and isset($this->route) ) $route = $this->route;
    
    // Initialize the result, and merge any supplemental data.
    $result = array_merge([], $this->merge, $merge);
    
    // Verify that a route was set.
    if( isset($route) ) {
    
      // Get the dynamic route-specific data.
      $data = $this->getDynamicRouteData($route);
      
      // Exit immediately for invalid endpoints.
      if( !$data ) return false;
      
      // Save the data.
      $result = array_merge($result, $this->getDynamicRouteData($route));
      
    }

    // Return the result.
    return $result;
    
  }
  
}

?>