<?php

// Use dependencies.
use LightnCandy\LightnCandy;
use SuperClosure\Serializer;

// Build the templating engine's `Parser` class.
class Parser {
  
  // Capture data.
  private $data;
  private $raw;
  private $parsed;
  
  // Capture configurations.
  protected $config;
  
  // Load engines.
  protected $handlebars;
  protected $serializer;
  
  // Load helpers.
  private $helpers = [];
  
  // Constructor
  function __construct( Config $config ) {
    
    // Save the configurations.
    $this->config = $config;
    
    // Load the engines.
    $this->handlebars = new LightnCandy();
    $this->serializer = new Serializer();
    
    // Load helpers.
    $this->helpers = $this->load(dirname(__DIR__)."/helpers/autoload.php")(); 
   
  }
  
  // Build the options array for the compiler.
  private function options() {
    
    return [
      'helpers' => $this->helpers
    ];
    
  }
  
  // Read a file.
  private function read( $path ) {
    
    return file_get_contents($path);
    
  }
  
  // Load a file.
  private function load( $path ) {
    
    return (include $path);
    
  }
  
  // Save a file.
  private function save( $path, $data ) {
    
    return file_put_contents($path, $data);
    
  }
  
  // Get the date a file was modified.
  private function modified( $path ) {
    
    return filemtime($path);
    
  }
  
  // Determine which file is newer given two or more paths.
  private function newest() {
    
    // Initialize the result.
    $newest = [
      'path' => null,
      'modified' => -1
    ];
    
    // Get the file paths.
    $paths = func_get_args();
    
    // Check the date modified for each file.
    foreach( $paths as $path ) { 
      
      // Get the date modified.
      $modified = $this->modified($path);
      
      // Update the result.
      if( $modified >= $newest['modified'] ) $newest = ['path' => $path, 'modified' => $modified];
      
    }
    
    // Return.
    return $newest;
    
  }
  
  // Compile template data.
  private function compile( $paths ) {
    
    // Get the contents of the template file.
    $template = $this->read($paths['template']);
      
    // Compile the template.
    $php = $this->handlebars->compile($template, $this->options());
      
    // Save the compiled template to the cache.
    $this->save($paths['cached'], "<?php $php ?>");
    
    // Save the helper data.
    $this->saveHelpers();
    
  }
  
  // Save helpers.
  private function loadHelpers() {
    
    // Read the helper data.
    $helpers = json_decode($this->read($this->config->HELPERS), true);

    // Unserialize closures.
    foreach( $helpers as $key => $helper ) {
      
      $helpers[$key] = $this->serializer->unserialize($helper);
      
    }
    
    // Return unserialized.
    return $helpers;
    
  }
  
  // Load helpers.
  private function saveHelpers() {
    
    // Localize the helpers.
    $helpers = $this->helpers;
    
    // Serialize closures.
    foreach( $helpers as $key => $helper ) {
      
      $helpers[$key] = $this->serializer->serialize($helper);
      
    }

    // Save serialized.
    return $this->save($this->config->HELPERS, json_encode($helpers));
    
  }
  
  // Render some data.
  public function render( $template, $data ) {
    
    // Get extension data.
    $ext = $this->config->EXT;
    
    // Get template file data.
    $dirname = dirname($template) == '.' ? '' : dirname($template).'/';
    $basename = basename($template, $ext['template']).$ext['cache'];
   
    // Determine the file paths.
    $paths = [
      'template'  => "{$this->config->TEMPLATES}/$template",
      'cached'    => "{$this->config->CACHE}/{$dirname}{$basename}"
    ];

    // Verify that the template exists.
    if( !file_exists($paths['template']) ) {
      
      throw new Exception("Unable to load template `$template`. Please verify that the template exists and try again." );
      
    }
    
    // Initialize the flags.
    $useCached = false;
    $newHelpers = false;
    
    // Check whether or not the cached file can be used.
    if( file_exists($paths['cached']) ) {
      
      $useCached = $this->newest($paths['template'], $paths['cached'])['path'] == $paths['cached'];
      
    }
  
    // Check whether or not new helpers were added.
    if( file_exists($this->config->HELPERS) ) {
      
      $newHelpers = !array_equiv($this->loadHelpers(), $this->helpers);
      
    }

    // Compile the template if no cached file is available.
    if( !$useCached or $newHelpers ) $this->compile($paths);
    
    // Load the renderer.
    $renderer = $this->load($paths['cached']); 
    
    // Render the template with the given data.
    return $renderer($data);
    
  }
  
}

?>