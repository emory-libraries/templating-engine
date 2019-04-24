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
    
    // Determine the cache path for the compiled template.
    $path = "templates/{$endpoint->eid}".CONFIG['ext']['cache'];
    
    // If a cached version of the template exists, see if it needs to be recompiled.
    // FIXME: Is this performant enough, or should we implement more advanced caching for compiled templates? Currently, we're compiling templates when first requested and only recompiling when the template pattern has changed. Refer to the [LightnCandy docs](https://zordius.github.io/HandlebarsCookbook/9000-quickstart.html) for best practices in terms of rendering.
    if( Cache::exists($path) ) {
      
      // Get the template pattern's last modified time.
      $modified = File::modified($endpoint->template->file);
      
      // Determine if the cached template is outdated, and recompile it if so.
      if( Cache::outdated($path, $modified) ) {
        
        // Recompile the template.
        $compiled = self::compile(self::prepare($endpoint));
        
        // Overwrite the cached template with the newly recompiled template.
        Cache::write($path, $compiled);
        
      }
      
    }
    
    // Otherwise, compile the template for the first time.
    else {

      // Compile the template.
      $compiled = self::compile(self::prepare($endpoint));
    
      // Save the compiled template to the cache.
      Cache::write($path, $compiled);
      
    }
    
    // If the endpoint is an asset, return the proper headers to render the asset.
    if( $endpoint->asset ) header("Content-Type: {$endpoint->mime}");
    
    // Get the template renderer.
    $renderer = Cache::include($path);
    
    // Render the template with the given data.
    return $renderer($endpoint->data);
    
  }
  
  // Prepare a template to be compiled.
  public static function prepare( Endpoint $endpoint ) {
    
    // Get the template.
    $template = $endpoint->template->template;
    
    // Prepare assets for compiling.
    if( $endpoint->asset ) {
      
      // Replace any file placeholders within the template with the source file's contents.
      $template = str_replace('{{file}}', File::read($endpoint->file), $template);
      
    }
    
    // Otherwise, prepare pages for compiling.
    else {
    
      // Get the layout wrapper ID.
      $id = isset($endpoint->template->wrapper) ? $endpoint->template->wrapper : 'default';

      // Get the layout wrapper.
      $wrapper = array_get(CONFIG['layouts'], $id, '{{template}}');

      // Compile the template with the wrapper.
      $template = str_replace('{{template}}', $template, $wrapper);
      
    }
    
    // Return the prepared template.
    return $template;
    
  }
  
  // Compiles a template string.
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