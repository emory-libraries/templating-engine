<?php

// Initialize modifier methods.
trait Icon_Modifiers {
  
  // Identify SVG elements that can be colored.
  public $colorable = [
    'altGlyph',
    'circle',
    'ellipse',
    'path',
    'polygon',
    'polyline',
    'rect',
    'text',
    'textPath',
    'tref',
    'tspan'
  ];
  
  // Recolor the icon.
  public function color( $color ) {
    
    // Build the fill regexes.
    $regex = '/fill="#[0-9a-f]+?"/i';
    
    // Recolor the image if a color was already set.
    if( preg_match($regex, $this->svg) ) {
      
      // Replace the color.
      $this->svg = preg_replace($regex, "fill=\"#${color}\"", $this->svg);
      
    }
    
    // Otherwise, add the color.
    else {
      
      // Initialize a regex for finding whole tags.
      $regex = '/<.+?>/';
      
      // Split the SVG by tags.
      preg_match_all($regex, $this->svg, $svg);
      
      // Get the exploded SVG.
      $svg = $svg[0];
      
      // Handle each SVG element one by one.
      foreach( $svg as &$el ) {
        
        // Ignore elements with a fill set to `none`.
        if( preg_match('/fill="none"/', $el) ) continue;

        // Add a fill color to any colorable SVG elements.
        foreach( $this->colorable as $tag ) {

          // Build a regex for determining if the current element is colorable.
          $regex = "/<{$tag}/";
          
          // Skip non-colorable elements.
          if( !preg_match($regex, $el) ) continue;

          // Apply a color to the element if colorable.
          $el = preg_replace($regex, "<{$tag} fill=\"#{$color}\"", $el);

        }
        
      }
      
      // Merge and save the SVG.
      $this->svg = implode('', $svg);
      
    }
    
  }
  
  // Resize the icon.
  public function size( $width, $height = null ) {
    
    // Build a regex for width and height.
    $w = '/<svg (.*?) width="\d*"(.*?)>/';
    $h = '/<svg (.*?) height="\d*"(.*?)>/';
    
    // Set the height to the width if none was given.
    if( !isset($height) ) $height = $width;
    
    // Resize the width.
    $this->svg = preg_replace($w, "<svg $1 width=\"{$width}\"$2>", $this->svg);
    
    // Resize the height.
    $this->svg = preg_replace($h, "<svg $1 height=\"{$height}\"$2>", $this->svg);
    
  }
  
}

// Icon - Loads and modifies an icon.
class Icon {
  
  // Load modifiers.
  use Icon_Modifiers;
  
  // Capture the icon path.
  protected $path;
  
  // Capture the icon file data.
  public $svg;
  
  // Constructor
  function __construct( $path ) {
    
    // Save the path.
    $this->path = $path;
    
    // Read the file.
    $this->svg = file_get_contents($this->path);
    
  }
  
}

?>