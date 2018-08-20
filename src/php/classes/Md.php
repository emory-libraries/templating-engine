<?php

// Build the templating engines custom markdown parser.
class Md extends ParsedownExtra {
  
  // Enables safe mode to prevent the use of HTML within markdown.
  public $useSafeMode = true;
  
  // Enables automatic header IDs by default.
  public $enableHeaderIds = true;
  
  // Overwrite existing IDs when automatically generating header IDS.
  public $overwriteHeaderIds = true;
  
  // Sets default header leavel to start with.
  public $headerLevelStart = 1;
  
  // Disables images within markdown.
  public $disableImages = true;

  // Constructor
  function __construct( $config = [] ) {
    
    // Initialize the parent constructor per usual.
    parent::__construct();
    
    // Merge any configurations that are passed in.
    foreach( $config as $key => $value ) {
      
      // Check that the configuration exists.
      if( property_exists($this, $key) ) {
        
        // Set the configuration's value.
        $this->{$key} = $value;
        
      }
      
    }
    
    // Toggle safe mode.
    if( $this->useSafeMode ) $this->setSafeMode(true);
    
  }
  
  // Automatically generate header IDs.
  private function __generateHeaderIds( &$header ) {
                                                    
    // Check for an existing ID.
    if( isset($header['element']['attributes']['id']) ) {
      
      // Only continue if overwriting is permitted.
      if( !$this->overwriteHeaderIds ) return;
      
    }

    // Generate a header ID from the header text.
    $header['element']['attributes']['id'] = str_attr($header['element']['handler']['argument']);
    
  }
  
  // Force header levels to start at the set level.
  private function __forceHeaderLevels( &$header ) {
    
    // Skip if header levels are set to their default.
    if( $this->headerLevelStart <= 1 ) return;
    
    // Get the header's level.
    $level = (int) str_replace('h', '', $header['element']['name']); 
    
    // Increment all header levels accordingly.
    $level = $this->headerLevelStart - 1 + $level;
    
    // Save the new header level.
    $header['element']['name'] = "h{$level}";
    
  }
  
  // Extend header blocks.
  protected function blockHeader( $excerpt ) {
    
    // Build the header per usual.
    $header = parent::blockHeader($excerpt);
    
    // Force header levels.
    $this->__forceHeaderLevels($header);
    
    // Automatically generate header IDs if enabled.
    if( $this->enableHeaderIds ) $this->__generateHeaderIds($header);
    
    // Load the header.
    return $header;
    
  }
  
  // Extend images.
  protected function inlineImage( $excerpt ) { 
    
    // Disable all images.
    if( $this->disableImages ) return;
    
    // Otherwise, build images per usual.
    return parent::inlineImage($excerpt);
    
  }
  
}

?>