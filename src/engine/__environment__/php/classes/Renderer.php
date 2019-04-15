<?php

// Use dependencies.
use LightnCandy\LightnCandy;
use SuperClosure\Serializer;

/*
 * Renderer
 *
 * Renders a page given a page template and some data.
 */
class Renderer {
  
  // Set flags for the handlebars engine.
  public static $flags = LightnCandy::FLAG_HANDLEBARSJS |
                         LightnCandy::FLAG_THIS |
                         LightnCandy::FLAG_ELSE |
                         LightnCandy::FLAG_RUNTIMEPARTIAL |
                         LightnCandy::FLAG_NAMEDARG |
                         LightnCandy::FLAG_PARENT |
                         LightnCandy::FLAG_ADVARNAME |
                         LightnCandy::FLAG_JSLENGTH |
                         LightnCandy::FLAG_SPVARS;
  
  // Get custom handlebars helpers.
  public static $helpers = CONFIG['handlebars']['helpers'];
  
  // Get handlebars partials.
  public static $partials = CONFIG['handlebars']['partials'];
  
  // Renders a page from the given template and data.
  public static function render( Endpoint $endpoint ) {
    
    // Initialize the handlebars engine.
    $handlebars = new LightnCandy();
    
    // Compile the template.
    $compiled = self::compile($endpoint->template);
    
    // Get the cache path for the compiled template.
    $path = "templates/{$endpoint->eid}".CONFIG['ext']['cache'];
    
    // Save the compiled template to the cache.
    // TODO: Create the Cache class.
    Cache::write($path, $compiled);
    
    // Get the template renderer.
    // FIXME: Is this too unperformant, or should we implement caching for compiled templates? Currently, we're recompiling templates for every incoming request. Refer to the [LightnCandy docs](https://zordius.github.io/HandlebarsCookbook/9000-quickstart.html) if we need to implement caching.
    $renderer = Cache::include($path);
    
    // Render the template with the given data.
    return $renderer($endpoint->data);
    
  }
  
  // Compiles a template file.
  public static function compile( string $template ) {
    
    // Initialize the handlebars engine.
    $handlebars = new LightnCandy();
    
    // Compile the template to PHP.
    $php = "<?php ".$handlebars->compile($template, [
      'flags' => self::$flags,
      'helpers' => self::$helpers,
      'partials' => self::$partials
    ])." ?>";
 
    // Return the compiled template.
    return $php;
    
  }
  
}

?>