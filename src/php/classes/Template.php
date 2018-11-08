<?php

// Initialize utility methods.
trait Template_Utilities {
  
  // Get a template's default path from an ID.
  private function __getTemplatePath( $id ) {
    
    return "{$this->config->TEMPLATES}/{$id}{$this->config->EXT['template']}";
    
  }
  
  // Get some metadata about a template file.
  private function __getTemplateFileMeta( $id ) {
    
    // Derive the template's default path and path within the cache.
    $default = "{$this->config->TEMPLATES}/{$id}{$this->config->EXT['template']}";
    $cached = "{$this->config->CACHE_PATH['templates']}/{$id}{$this->config->EXT['cache']}";
   
    // Initialize the result.
    $result = false;
    
    // See if the template path exists.
    if( file_exists($default) ) {

      // Otherwise, return the result.
      $result = [
        'template' => $default,
        'cache' => $cached
      ];
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Get the real paths for a template file.
  private function __getTemplateFilePaths( $id ) {
    
    // Initialize the result.
    $result = false;
    
    // Check for an array of IDs, and treat them as an order of precendence.
    if( is_array($id) ) {
      
      // Find the first available template.
      foreach( $id as $target ) {
        
        // Attempt to get the template file metadata.
        $result = $this->__getTemplateFileMeta($target);
          
        // Stop looking for other templates if some metadata was found.
        if( $result !== false ) break;
        
      }
      
    }
    
    // Otherwise, use the single ID that's given.
    else $result = $this->__getTemplateFileMeta($id);
    
    // Return the result.
    return $result;
    
  }
  
}

// Initialize cache methods.
trait Template_Cache {
  
  // Initialize the cache.
  private $cache;
  
  // Initialize the cache.
  private function __initCache() {
    
    // Initialize the cache.
    $this->cache = new Cache($this->config->CACHE);
    
  }
  
}

// Creates a `Template` class for reading templates.
class Template {
  
  // Load traits.
  use Template_Utilities, Template_Cache;
  
  // Capture configurations.
  protected $config;
  
  // Capture the template ID.
  private $id;
  
  // Constructor
  function __construct( $id = null ) {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations.
    $this->config = $config;
    
    // Capture the ID.
    $this->id = $id;
    
    // Initialize the cache.
    $this->__initCache();
    
  }
  
  // Get a template by ID.
  public function getTemplate( $id = null ) {
    
    // Set the ID if not already set.
    if( !isset($id) and isset($this->id) ) $id = $this->id;
    
    // Return the template.
    return $this->__getTemplateFilePaths($id);
    
  }
  
}

?>