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
    $this->data['META'] = "{$this->ROOT}/meta";
    $this->data['CACHE'] = dirname(__DIR__).'/cache';
    $this->data['PATTERNS'] = "{$this->ROOT}/patterns";
    $this->data['ATOMS'] = "{$this->PATTERNS}/atoms";
    $this->data['MOLECULES'] = "{$this->PATTERNS}/molecules";
    $this->data['ORGANISMS'] = "{$this->PATTERNS}/organisms";
    $this->data['TEMPLATES'] = "{$this->PATTERNS}/templates";
    $this->data['DATA'] = "{$this->ROOT}/data";
    $this->data['EXT'] = [
      'template'  => '.handlebars',
      'data'      => '.json',
      'cache'     => '.php'
    ];
    $this->data['HELPERS'] = "{$this->CACHE}/.helpers.json";
    $this->data['PARTIALS'] = [
      'atoms'     => $this->ATOMS,
      'molecules' => $this->MOLECULES,
      'organisms' => $this->ORGANISMS
    ];
    $this->data['CACHED_PARTIALS'] = "{$this->CACHE}/partials";
    $this->data['CACHED_TEMPLATES'] = "{$this->CACHE}/templates";
    
    // Load meta data.
    $this->loadMeta();
    
  }
  
  // Load meta data.
  private function loadMeta() {
    
    // Scan the contents of the meta directory.
    $metas = scandir_clean($this->META);
    
    // Load meta data.
    foreach( $metas as $meta ) {
      
      // Extract the base name.
      $name = strtoupper(basename($meta, '.json'));
      
      // Read the meta data.
      $this->data[$name] = json_decode(file_get_contents("{$this->META}/{$meta}"), true);
      
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