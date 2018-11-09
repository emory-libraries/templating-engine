<?php

// Build the templating engines custom markdown parser.
class Markdown extends ParsedownExtra {
  
  // Capture configurations.
  protected $config;

  // Constructor
  function __construct( $overrides = [] ) {
    
    // Set overrides if not set.
    if( !isset($overrides) ) $overrides = [];
    
    // Initialize the parent constructor per usual.
    parent::__construct();
    
    // Use global configurations.
    global $config;
    
    // Save the configurations.
    $this->config = $config->MARKDOWN;

    // Override any default configurations with the ones that are passed in.
    foreach( $overrides as $key => $value ) {
      
      // Check that the configuration exists.
      if( array_key_exists($key, $this->config) ) {
        
        // Set the configuration's value.
        $this->__setConfig($key, (isset($value) ? $value : $this->__getConfig($key)));
        
      }
      
    }
    
    // Get the safe mode configuration.
    $useSafeMode = $this->__getConfig('useSafeMode');
    
    // Toggle safe mode.
    if( isset($useSafeMode) and $useSafeMode === true ) $this->setSafeMode(true);
    
  }
  
  // Get a configuration.
  private function __getConfig( $key ) { return array_get($this->config, $key); }
  
  // Set a configuration.
  private function __setConfig( $key, $value ) { $this->config[$key] = $value; }
  
  // Automatically generate header IDs.
  private function __generateHeaderIds( &$header ) {
    
    // Get the header ID configuration.
    $overwriteHeaderIds = $this->__getConfig('overwriteHeaderIds');
                                                    
    // Check for an existing ID.
    if( isset($header['element']['attributes']['id']) ) {
      
      // Only continue if overwriting is permitted.
      if( isset($overwriteHeaderIds) and $overwriteHeaderIds === false ) return;
      
    }

    // Generate a header ID from the header text.
    $header['element']['attributes']['id'] = strtoattr($header['element']['handler']['argument']);
    
  }
  
  // Force header levels to start at the set level.
  private function __forceHeaderLevels( &$header ) {
    
    // Get the header level configuration.
    $headerLevelStart = $this->__getConfig('headerLevelStart');
    
    // Skip if header levels are set to their default.
    if( !isset($headerLevelStart) or $headerLevelStart <= 1 ) return;
    
    // Get the header's level.
    $level = (int) str_replace('h', '', $header['element']['name']); 
    
    // Increment all header levels accordingly.
    $level = $headerLevelStart - 1 + $level;
    
    // Save the new header level.
    $header['element']['name'] = "h{$level}";
    
  }
  
  // Extend header blocks.
  protected function blockHeader( $excerpt ) {
    
    // Get the header ID configurations.
    $enableHeaderIds = $this->__getConfig('enableHeaderIds');
    
    // Build the header per usual.
    $header = parent::blockHeader($excerpt);
    
    // Force header levels.
    $this->__forceHeaderLevels($header);
    
    // Automatically generate header IDs if enabled.
    if( isset($enableHeaderIds) and $enableHeaderIds === true ) $this->__generateHeaderIds($header);
    
    // Load the header.
    return $header;
    
  }
  
  // Extend images.
  protected function inlineImage( $excerpt ) { 
    
    // Get the image configuration.
    $disableImages = $this->__getConfig('disableImages');
    
    // Disable all images.
    if( isset($disableImages) and $disableImages === true ) return;
    
    // Otherwise, build images per usual.
    return parent::inlineImage($excerpt);
    
  }
  
}

?>