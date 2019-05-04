<?php

// Use dependencies.
use LightnCandy\LightnCandy;

/*
 * Renderer
 *
 * Renders a page given a page template and some data.
 * Alternatively, this may render an error page or asset.
 */
class Renderer {
  
  // Prepare a template to be compiled.
  public static function prepare( string $template, $wrapper = 'default' ) {

    // Get the layout wrapper.
    $wrapper = array_get(CONFIG['layouts'], $wrapper, '{{template}}');

    // Compile the template with the wrapper.
    $template = str_replace('{{template}}', $template, $wrapper);
    
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
      'partials' => API::get('/partials')
    ];
    
    // Compile the template to a closure function.
    $closure = LightnCandy::compile($template, $config);
    
    // Compile the template to PHP.
    $php = "<?php $closure ?>";

    // Return the compiled template.
    return $php;
    
  }
  
  // Renders a page for the requested endpoint, given its data and template.
  public static function render( Endpoint $endpoint) { 
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Renderer', true);
    
    // Get the cache path for the compiled template.
    $path = $endpoint->route->cache;
    
    // Create a helper method for quickly compiling and saving a template to the cache.
    $compiler = function() use ($endpoint, $path) {
      
      // Get the template.
      $template = self::prepare($endpoint->template);
      
      // Compile the template.
      $compiled = self::compile($template);

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
        $modified = File::modified($template->file);

        // Determine if the cached template is outdated, and recompile it if so.
        if( Cache::outdated($path, $modified) ) $compiler();

      }

      // Otherwise, compile the template for the first time.
      else $compiler();
      
    }
    
    // Get the template's renderer.
    $renderer = Cache::include($path);
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::finish('Renderer');

    // Render the template with the given data.
    return $renderer($endpoint->data);
    
  }
  
  // Renders an error page.
  public static function error( Endpoint $endpoint ) {
    
    // Output the status code header.
    header('HTTP/1.0 '.$endpoint->error.' '.CONFIG['errors'][$endpoint->error]['status']);
    
    // Render the appropriate error page.
    return self::render($endpoint);
    
  }
  
  // Renders an asset file.
  public static function asset( Endpoint $endpoint ) {
    
    // Output a content type header.
    header('Content-Type: '.$endpoint->data['mime']);

    // Output the asset.
    readfile($endpoint->data['path']);
    
  }
  
}

?>