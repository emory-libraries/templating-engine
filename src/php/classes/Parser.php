<?php

// Use dependencies.
use LightnCandy\LightnCandy;
use SuperClosure\Serializer;

// Initialize utility methods.
trait Parser_Utilities {
  
  // Determines if a passed value is a reference.
  private function __isReference( $value ) {
    
    // Place the value inside an array.
    $array = [$value];
    
    // Get the value as it was passed into the function.
    $arg = func_get_arg(0); 
    
    // Determine if the two values match.
    return isset($arg[0]) and $arg === [$array[0]];
    
  }
  
  // Get flags by name from the Handlebars engine.
  private function __flag( $name ) { return constant(LightnCandy::class."::FLAG_{$name}"); }
  
  // Build the options array for the Handlebars engine's compiler.
  private function __options() {
    
    return [
      'flags' =>  $this->__flag('THIS') | 
                  $this->__flag('ELSE') |
                  $this->__flag('RUNTIMEPARTIAL') | 
                  $this->__flag('NAMEDARG') |
                  $this->__flag('PARENT') |
                  $this->__flag('ADVARNAME'),
      'helpers' => isset($this->helpers) ? $this->helpers : [],
      'partials'  => isset($this->partials) ? $this->partials : []
    ];
    
  }
  
}

// Initialize helper methods.
trait Parser_Helpers {
  
  // Initialize helpers.
  private $helpers = [];
  
  // Get helpers.
  private function __getHelpers() {
    
    // Autload the helpers.
    return (include cleanpath($this->config->HELPERS."/autoload.php"))();
    
  }
  
  // Get helpers from the cache.
  private function __getCachedHelpers() {
    
    // Initialize the result.
    $result = [];
    
    // Get the helpers path.
    $path = $this->config->CACHE_PATH['helpers'];
    
    // Verify that the path exists.
    if( $this->cache->has($path) ) {
    
      // Read the helper data.
      $result = json_decode($this->cache->get($path), true);

      // Unserialize closures.
      foreach( $result as $key => $helper ) { $result[$key] = $this->serializer->unserialize($helper); }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Add helpers to the cache.
  private function __addCachedHelpers( $helpers ) {
    
    // Serialize closures.
    foreach( $helpers as $key => $helper ) { $helpers[$key] = $this->serializer->serialize($helper); }

    // Save serialized.
    return $this->cache->add($this->config->CACHE_PATH['helpers'], json_encode($helpers));
    
  }
  
}

// Initialize partial methods.
trait Parser_Partials {
  
  // Initialize partials.
  private $partials = [];
  
  // Import a partial.
  private function __importPartial( $path ) {
    
    // Initialize the result.
    $result = [];
    
    // Get relative pattern path.
    $relative = trim(str_replace($this->config->PARTIALS, '', $path), '/');
    
    // Get the partial's extension.
    $ext = pathinfo($relative, PATHINFO_EXTENSION);
    
    // Get the partial's basename.
    $basename = basename($relative, ".$ext");
    
    // Get the partial's root directory.
    $root = explode('/', $relative)[0];
    
    // Derive the partial's type from it's root directory.
    $type = preg_replace('/^[0-9]+\-/', '', $root);
    
    // Get the partial's contents.
    $contents = file_get_contents($path);
    
    // Save the default include path.
    $default = $result[$relative] = $contents;
    
    // Add references for any complimentary include paths.
    $result["{$type}-{$basename}"] = &$default;
    
    // Return the result.
    return $result;
    
  }
  
  // Find partial patterns.
  private function __findPartials() {
    
    // Initialize the results.
    $result = [];
    
    // Get the partials path.
    $path = $this->config->PARTIALS;
    
    // Verify that the partials path exists.
    if( file_exists($path) ) {
    
      // Find all partial patterns.
      $result = array_values(array_filter(scandir_recursive($path), function($pattern) {

        // Get the templates directory.
        $templates = trim(str_replace($this->config->PARTIALS, '', $this->config->TEMPLATES), '/');

        // Exclude template patterns from partials.
        return strpos($pattern, $templates) !== 0;

      }));
  
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Get all partials.
  private function __getPartials() {
    
    // Initialize the results.
    $result = [];
    
    // Find all partial patterns.
    $partials = $this->__findPartials();
    
    // Import all partials.
    foreach( $partials as $path ) { $result = array_merge($result, $this->__importPartial("{$this->config->PARTIALS}/{$path}")); }
    
    // Return the results.
    return $result;
    
  }
  
  // Get all partials from the cache.
  private function __getCachedPartials() {
    
    // Initialize the result.
    $result = [];
    
    // Get the partial path.
    $path = $this->config->CACHE_PATH['partials'];
    
    // Verify that partials exist.
    if( $this->cache->has($path) ) {
    
      // Get all cached partials.
      $partials = $this->cache->scan($path);
    
      // Import all cached partials.
      foreach( $partials as $path ) { $result = array_merge($result, $this->__importPartial($this->cache->path($path))); }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Add partials to the cache.
  private function __addCachedPartials( $partials ) {
    
    // Remove any references.
    foreach( $partials as $key => $partial ) {
      
      // Remove the partial if it's a reference.
      if( $this->__isReference($partial) ) unset($partials[$key]);
      
    }
    
    // Save each partial to the cache.
    foreach( $partials as $path => $partial ) { $this->cache->add("{$this->config->CACHE_PATH['partials']}/{$path}", $partial);  }
    
  }
  
}

// Initialize cache methods.
trait Parser_Cache {
  
  // Initialize the cache.
  private $cache;
  
  // Initialize the cache.
  private function __initCache() { $this->cache = new Cache($this->config->CACHE); }
  
}


// Build the templating engine's `Parser` class.
class Parser {
  
  // Load traits.
  use Parser_Utilities, Parser_Helpers, Parser_Partials, Parser_Cache;
  
  // Capture data.
  private $data;
  private $raw;
  private $parsed;
  
  // Capture configurations.
  protected $config;
  
  // Load engines.
  protected $handlebars;
  protected $serializer;
  
  // Set flags.
  protected $useCached = false;
  protected $newHelpers = false;
  protected $newPartials = false;
  
  // Constructor
  function __construct() {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations.
    $this->config = $config;
    
    // Load the engines.
    $this->handlebars = new LightnCandy();
    $this->serializer = new Serializer();
    
    // Initialize the cache.
    $this->__initCache();
    
    // Get helpers and partials.
    $this->helpers = $this->__getHelpers(); 
    $this->partials = $this->__getPartials();
   
  }
  
  // Compile template data.
  private function compile( $paths ) {
    
    // Get the contents of the template file.
    $template = file_get_contents($paths['template']);

    // Compile the template.
    $php = $this->handlebars->compile($template, $this->__options());

    // Save the compiled template to the cache.
    $this->cache->add($paths['cache'], "<?php $php ?>");
    
  } 
  
  // Render some data.
  public function render( $template, $data ) {
    
    // Initialize flags.
    $flag = [
      'USE_CACHE'     => false,
      'NEW_HELPERS'   => false,
      'NEW_PARTIALS'  => false
    ];
    
    // Determine if a cached template file can be used.
    if( $this->cache->has($template['cache']) ) {
     
      // Determine whether or not the cache file should be used.
      $flag['USE_CACHE'] = $this->cache->newer(...array_values($template));
      
    }
  
    // Determine if any new helpers were added.
    if( file_exists($this->config->HELPERS) ) {
      
      $flag['NEW_HELPERS'] = !array_equiv($this->__getCachedHelpers(), $this->helpers);
      
    }
    
    // Determine if any new partials were added.
    if( count($this->__findPartials()) > 0 ) {
      
      $flag['NEW_PARTIALS'] = !array_equiv($this->__getCachedPartials(), $this->partials);
      
    }

    // Recompile the template if there's no cached file or new helpers or partials were detected.
    if( !$flag['USE_CACHE'] or $flag['NEW_HELPERS'] or $flag['NEW_PARTIALS'] ) $this->compile($template);
    
    // Load the file's renderer from the cache.
    $render = include $this->cache->path($template['cache']); 
    
    // Render the template with the given data.
    return $render($data);
    
  }
  
}

?>