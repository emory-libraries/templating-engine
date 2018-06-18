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
  
  // Load compiler options.
  private $helpers = [];
  private $partials = [];
  
  // Constructor
  function __construct( Config $config ) {
    
    // Save the configurations.
    $this->config = $config;
    
    // Load the engines.
    $this->handlebars = new LightnCandy();
    $this->serializer = new Serializer();
    
    // Get helpers and partials.
    $this->getHelpers(); 
    $this->getPartials();
   
  }
  
  // Build the options array for the compiler.
  private function options() {
    
    return [
      'helpers' => $this->helpers,
      'partials'  => $this->partials
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
  
  // Get partials.
  private function getPartials() {
    
    // Get paths to partials.
    $paths = $this->config->PARTIALS;
    
    // Look for partials.
    foreach( $paths as $type => $path ) {
      
      // Deep scan the contents of the directory.
      $partials = scandir_recursive($path); 
      
      // Build all partials.
      foreach( $partials as $partial ) {
        
        // Get the name of the partial.
        $name = basename($partial, $this->config->EXT['template']);
        
        // Read the partial.
        $this->partials["$type-$name"] = $this->read("$path/$partial");
        
      }
      
    }
    
  }
  
  // Get helpers.
  private function getHelpers() {
    
    $this->helpers = $this->load(dirname(__DIR__)."/helpers/autoload.php")();
    
  }
  
  // Load helpers from cache.
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
  
  // Save helpers to cache.
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