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
  
  // Set flags.
  protected $useCached = false;
  protected $newHelpers = false;
  protected $newPartials = false;
  
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
    
    // Initialize the cache.
    $this->cache();
   
  }
  
  // Build the options array for the compiler.
  private function options() {
    
    return [
      'helpers' => $this->helpers,
      'partials'  => $this->partials
    ];
    
  }
  
  // Initialize the cache.
  private function cache() {
    
    // Get the cache paths.
    $paths = [
      'templates' => $this->config->CACHED_TEMPLATES,
      'partials'  => $this->config->CACHED_PARTIALS
    ];
    
    // Make the template directory if it doesn't already exist.
    if( !file_exists($paths['templates']) ) mkdir($paths['templates']);
    
    // Make the partial directory if it doesn't already exist.
    if( !file_exists($paths['partials']) ) mkdir($paths['partials']);
    
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
  
  // Determine which file is newer given two or more paths.
  private function newest( array $paths ) {
    
    // Initialize result.
    $newest = [
      'path' => null,
      'modified' => -1
    ];
    
    // Check the date modified for each file.
    foreach( $paths as $path ) { 
      
      // Update the result.
      if( $path['modified'] >= $newest['modified'] ) $newest = $path;
      
    }
    
    // Return.
    return $newest;
    
  }
  
  // Compile template data.
  private function compile( $paths ) {
    
    // Get the contents of the template file.
    $template = $this->read($paths['template']['path']);
      
    // Compile the template.
    $php = $this->handlebars->compile($template, $this->options());
      
    // Save the compiled template to the cache.
    $this->save($paths['cache']['path'], "<?php $php ?>");
    
    // Save the helpers and partials.
    $this->saveHelpers();
    $this->savePartials();
    
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
  
  // Load partials from cache.
  private function loadPartials() {
    
    // Get the path to the partials.
    $path = $this->config->CACHED_PARTIALS;
    
    // Get the cached partials.
    $partials = scandir_clean($path);
    
    // Read all cached partials.
    foreach( $partials as $key => $partial ) {
      
      // Get the name of the partial.
      $name = basename($partial, $this->config->EXT['template']);
      
      // Read the partial.
      $partials[$name] = $this->read("$path/$partial");
      
      // Delete the old key.
      unset($partials[$key]);
      
    }
    
    // Return partials.
    return $partials;
    
  }
  
  // Save helpers to cache.
  private function savePartials() {
    
    // Localize the partials.
    $partials = $this->partials;
    
    // Save each partial.
    foreach( $partials as $name => $partial ) {
      
      // Save the partial.
      $this->save("{$this->config->CACHED_PARTIALS}/{$name}{$this->config->EXT['template']}", $partial);
      
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
    
    // Check whether or not the cached file can be used.
    if( $template['cache']['active'] ) {
      
      $this->useCached = $this->newest($template)['path'] == $template['cache']['path'];
      
    }
  
    // Check whether or not new helpers were added.
    if( file_exists($this->config->HELPERS) ) {
      
      $this->newHelpers = !array_equiv($this->loadHelpers(), $this->helpers);
      
    }
    
    // Check whether or not cached partials exist.
    if( count(scandir_clean($this->config->CACHED_PARTIALS)) > 0 ) {
      
      $this->newPartials = !array_equiv($this->loadPartials(), $this->partials);
      
    }

    // Compile the template if no cached file is available.
    if( !$this->useCached or $this->newHelpers or $this->newPartials ) $this->compile($template);
    
    // Load the renderer.
    $renderer = $this->load($template['cache']['path']); 
    
    // Render the template with the given data.
    return $renderer($data['data']);
    
  }
  
}

?>