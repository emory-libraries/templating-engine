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
  
  // Renders a page from the given template and data.
  public static function render( Endpoint $endpoint ) { 
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Renderer', true);
    
    // Determine the cache path for the compiled template.
    $path = "templates/{$endpoint->eid}".CONFIG['ext']['cache'];
    
    // Create a helper method for quickly compiling and saving a template to the cache.
    $compiler = function() use ($endpoint, $path) {
      
      // Compile the template.
      $compiled = self::compile(self::prepare($endpoint));

      // Save the compiled template to the cache.
      Cache::write($path, $compiled);
      
      // Add benchmark point.
      if( DEVELOPMENT ) Performance\Performance::point('Template compiled.');
      
    };
    
    // Skip caching when in development mode, and always recompile patterns.
    if( CONFIG['development'] ) $compiler();
    
    // Otherwise, use caching when not in development mode.
    else {
    
      // If a cached version of the template exists, see if it needs to be recompiled.
      // FIXME: Is this performant enough, or should we implement more advanced caching for compiled templates? Currently, we're compiling templates when first requested and only recompiling when the template pattern has changed. Refer to the [LightnCandy docs](https://zordius.github.io/HandlebarsCookbook/9000-quickstart.html) for best practices in terms of rendering.
      if( Cache::exists($path) ) {

        // Get the template pattern's last modified time.
        $modified = File::modified($endpoint->template->file);

        // Determine if the cached template is outdated, and recompile it if so.
        if( Cache::outdated($path, $modified) ) $compiler();

      }

      // Otherwise, compile the template for the first time.
      else $compiler();
      
    }
    
    // If the endpoint is an asset, return the proper headers to render the asset.
    if( $endpoint->asset ) header("Content-Type: {$endpoint->mime}");
    
    // Get the template renderer.
    $renderer = Cache::include($path);
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::finish('Renderer');

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
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Template prepared.');
    
    // Return the prepared template.
    return $template;
    
  }
  
  // Compiles a template string.
  public static function compile( string $template ) {
    
    // Initialize a hleper method for getting flag constants.
    $flag = function( $flag ) {
      
      // Return the flag constant.
      return constant("LightnCandy\LightnCandy::$flag");
      
    };
    
    // Get the handlebars engine configurations.
    $config = [
      'flags' => array_reduce(array_tail(CONFIG['handlebars']['flags']), function($a, $b) use ($flag) {
        
        // Merge all flags together.
        return $a | $flag($b);
        
      }, $flag(array_first(CONFIG['handlebars']['flags']))),
      'helpers' => CONFIG['handlebars']['helpers'],
      'partials' => CONFIG['handlebars']['partials']
    ];
    
    // Compile the template to a closure function.
    $closure = LightnCandy::compile($template, $config);
    
    // Compile the template to PHP.
    $php = "<?php $closure ?>";

    // Return the compiled template.
    return $php;
    
  }
  
}

?>