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
    $this->data['CACHE'] = dirname(__DIR__).'/cache';
    $this->data['TEMPLATES'] = "{$this->ROOT}/patterns/templates";
    $this->data['DATA'] = "{$this->ROOT}/data";
    $this->data['EXT'] = [
      'template'  => '.handlebars',
      'data'      => '.json',
      'cache'     => '.php'
    ];
    $this->data['HELPERS'] = "{$this->CACHE}/.helpers.json";
    
    // Load meta data.
    $this->meta();
    
  }
  
  // Load meta data.
  private function meta() {
    
    // Scan the contents of the meta directory.
    $metas = array_filter(scandir("{$this->ROOT}/meta/"), function($meta) {
      
      return !in_array($meta, ['.', '..']);
      
    });
    
    // Load meta data.
    foreach( $metas as $meta ) {
      
      // Extract the base name.
      $basename = basename($meta);
      
      // Read the meta data.
      $this->data[strtoupper($basename)] = json_decode(file_get_contents($meta));
      
    }
    
  }
  
  // Getter
  function __get( $key ) {
    
    return $this->data[$key];
    
  }
  
  // Setter
  function __set( $key, $value ) {
    
    $this->data[$key] = $value;
    
  }
  
}

?>