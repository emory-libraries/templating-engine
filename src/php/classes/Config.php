<?php

// Create a `Config` class.
class Config {
  
  // Define data.
  private $data = [];
  
  // Constructor
  function __construct() {
    
    // Set initial configuration data.
    $this->data['ROOT'] = dirname(dirname(__DIR__)); 
    $this->data['ROOT_PATH'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->ROOT); 
    
    // Set cache configuration data.
    $this->data['CACHE'] = dirname(__DIR__).'/cache';
    $this->data['CACHE_PATH'] = [
      
      // Specify a subdirectory for partials to be stored, or use an empty string to store files at the cache's root directory.
      "partials" => "/partials",
      
      // Specify a subdirectory for templates to be stored, or use an empty string to store files at the cache's root directory.
      "templates" => "/templates",
      
      // Specify a filename for helper data to be stored. This file will be encoded as JSON.
      "helpers" => ".helpers.json"
      
    ];
    
    // Set preferred extension configurations. This is mostly used for caching.
    $this->data['EXT'] = [
      'template'  => '.handlebars',
      'data'      => '.json',
      'cache'     => '.php'
    ];
    
    // Set data file configurations.
    $this->data['DATA'] = "{$this->ROOT}/data";
    $this->data['DATA_GLOBAL'] = "{$this->DATA}/_global";
    $this->data['DATA_META'] = "{$this->DATA}/_meta";
    
    // Set template file configurations.
    $this->data['PATTERNS'] = "{$this->ROOT}/patterns";
    $this->data['TEMPLATES'] = "{$this->PATTERNS}/templates";
    
    // Set parser configurations.
    $this->data['HELPERS'] = dirname(__DIR__)."/helpers";
    $this->data['PARTIALS'] = [
      'atoms'     => $this->PATTERNS_ATOMS,
      'molecules' => $this->PATTERNS_MOLECULES,
      'organisms' => $this->PATTERNS_ORGANISMS
    ];
    
  }
  
  // Getter
  function __get( $key ) { return array_get($this->data, $key); }
  
  // Setter
  function __set( $key, $value ) { $this->data[$key] = $value; }
  
}

?>