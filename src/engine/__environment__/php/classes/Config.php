<?php

// Create a `Config` class.
class Config {
  
  // Define data.
  private $data = [];
  
  // Constructor
  function __construct() {
    
    // Configure root paths.
    $this->data['ROOT'] = dirname(dirname(__DIR__)); 
    $this->data['ROOT_PATH'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->ROOT); 
    
    // Configure the cache.
    $this->data['CACHE'] = dirname(__DIR__).'/cache';
    $this->data['CACHE_PATH'] = [
      
      // Specify a subdirectory for partials to be stored, or use an empty string to store files at the cache's root directory.
      "partials" => "/partials",
      
      // Specify a subdirectory for templates to be stored, or use an empty string to store files at the cache's root directory.
      "templates" => "/templates",
      
      // Specify a filename for helper data to be stored. This file will be encoded as JSON.
      "helpers" => ".helpers.json"
      
    ];
    
    // Configure default file extensions used for generated files.
    $this->data['EXT'] = [
      'template'  => '.hbs',
      'data'      => '.json',
      'cache'     => '.php'
    ];
    
    // Configure the data stores.
    $this->data['DATA'] = "{$this->ROOT}/data";
    $this->data['DATA_GLOBAL'] = "{$this->DATA}/_global";
    $this->data['DATA_META'] = "{$this->DATA}/_meta";
    
    // Configure the handlebars processor.
    $this->data['PARTIALS'] = "{$this->ROOT}/patterns";
    $this->data['TEMPLATES'] = "{$this->PARTIALS}/templates";
    $this->data['HELPERS'] = dirname(__DIR__)."/helpers";
    
    // Configure the markdown processor.
    $this->data['MARKDOWN'] = [
      
      // Enables safe mode to prevent the use of HTML within markdown.
      'useSafeMode' => true,
      
      // Enables automatic header IDs by default.
      'enabledHeaderIds' => true,
      
      // Overwrite existing IDs when automatically generating header IDs.
      'overwriteHeaderIds' => true,
      
      // Sets default header level to start with the given value.
      'headerLevelStart' => 2,
      
      // Disables the use of images within markdown.
      'disableImages' => true
      
    ];
    
  }
  
  // Getter
  function __get( $key ) { return array_get($this->data, $key); }
  
  // Setter
  function __set( $key, $value ) { $this->data[$key] = $value; }
  
}

?>