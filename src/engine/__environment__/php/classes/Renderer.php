<?php

// Use dependencies.
use LightnCandy\LightnCandy;
use LightnCandy\Runtime;
use Engine\API;
use Performance\Performance;

/*
 * Renderer
 *
 * Renders a page given a page template and some data.
 * Alternatively, this may render an error page or asset.
 */
class Renderer {
  
  // Defines keep alive time constants for caching assets in the browser.
  const KEEP_ALIVE_MINUTE = 60;
  const KEEP_ALIVE_HOUR = 3600;
  const KEEP_ALIVE_DAY = 86400;
  const KEEP_ALIVE_WEEK = 604800;
  const KEEP_ALIVE_MONTH = 2628000;
  const KEEP_ALIVE_YEAR = 31536000;
  
  // Prepare a template to be compiled.
  public static function prepare( string $template, $wrapper = 'default', $benchmarking = BENCHMARKING ) {

    // Get the layout wrapper.
    $wrapper = self::layout($wrapper);

    // Compile the template with the wrapper.
    $template = str_replace('{{template}}', $template, $wrapper);
    
    // Add benchmark point.
    if( $benchmarking ) Performance::point('Template prepared.');
    
    // Return the prepared template.
    return $template;
    
  }
  
  // Compiles a template string.
  public static function compile( string $template ) {
    
    // Initialize a helper method for getting flag constants.
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
      'helpers' => API::get('/helpers'),
      'partials' => API::get('/partials')
    ];
    
    // Try to compile the template.
    try {
    
      // Compile the template to a closure function.
      $closure = LightnCandy::compile($template, $config);

      // Compile the template to PHP.
      $php = "<?php $closure ?>";

      // Return the compiled template.
      return $php;
      
    // Otherwise, throw a failure.
    } catch( Throwable $exception ) { 
      
      // Throw a failure.
      throw new Failure(520, $exception);
      
    }
    
  }
  
  // Renders a page for the requested endpoint, given its data and template.
  public static function render( Endpoint $endpoint, $benchmarking = BENCHMARKING ) { 
    
    // Add benchmark point.
    if( $benchmarking ) Performance::point('Renderer', true);
    
    // Get the cache path for the compiled template.
    $path = $endpoint->route->cache;
    
    // Create a helper method for quickly compiling and saving a template to the cache.
    $compiler = function() use ($endpoint, $path, $benchmarking) {
      
      // Determine the appropriate wrapper to use.
      $wrapper = DEVELOPMENT ? 'development' : 'default';
      
      // Get the template.
      $template = self::prepare($endpoint->template, $wrapper, $benchmarking);
      
      // Compile the template.
      $compiled = self::compile($template);

      // Save the compiled template to the cache.
      Cache::write($path, $compiled);
      
      // Add benchmark point.
      if( $benchmarking ) Performance::point('Template compiled.');
      
    };
    
    // Skip caching when in development mode, and always recompile patterns.
    if( DEVELOPMENT ) $compiler();
    
    // Otherwise, use caching when not in development mode.
    else {
    
      // If a cached version of the template exists, see if it needs to be recompiled.
      // FIXME: Is this performant enough, or should we implement more advanced caching for compiled templates? Currently, we're compiling templates when first requested and only recompiling when the template pattern has changed. Refer to the [LightnCandy docs](https://zordius.github.io/HandlebarsCookbook/9000-quickstart.html) for best practices in terms of rendering.
      if( Cache::exists($path) ) {

        // Get the template pattern's file if available.
        $template = $endpoint->pattern->file;
        
        // If the template has a pattern file, then determine if it needs to be recompiled.
        if( isset($template) ) {
        
          // Get the template pattern's last modified time.
          $modified = File::modified($template);

          // Determine if the cached template is outdated, and recompile it if so.
          if( Cache::outdated($path, $modified) ) $compiler();
          
        }

      }

      // Otherwise, compile the template for the first time.
      else $compiler();
      
    }
    
    // Get the template's renderer.
    $renderer = Cache::include($path);
    
    // Add benchmark point.
    if( $benchmarking ) Performance::finish('Renderer');
    
    // Output a content type header.
    header('Content-Type: '.Mime::type('html'));

    // Attempt to render the page.
    try {
    
      // Render the template with the given data.
      $page = $renderer($endpoint->data->data);
      
      // Return the page's contents.
      return $page;
      
    // If rendering fails, then throw an error page.
    } catch( Throwable $exception ) {
      
      // Throw an error page.
      throw new Failure(521, $exception);
      
    }
    
  }
  
  // Renders an error page.
  public static function error( Endpoint $endpoint, $benchmarking = BENCHMARKING ) {
    
    // Output the status code header.
    header('HTTP/1.0 '.$endpoint->error.' '.CONFIG['errors'][$endpoint->error]['status']);
    
    // Render the appropriate error page.
    return self::render($endpoint, $benchmarking);
    
  }
  
  // Renders an asset file.
  public static function asset( Endpoint $endpoint ) {
    
    // Output a content type header.
    header('Content-Type: '.$endpoint->route->mime);
    
    // Send cache control headers.
    header('Cache-Control: max-age='.CONFIG['assetHeaders']['keepAlive'].', public');
    
    // For PHP files, include them.
    if( Path::extname($endpoint->endpoint) == 'php' ) include $endpoint->route->path;

    // Otherwise, output the asset.
    else readfile($endpoint->route->path);
    
  }
  
  // Get a layout wrapper by an ID.
  protected static function layout( $id ) {
    
    // Return the layout.
    return array_get(CONFIG['layouts'], $id, CONFIG['defaults']['layoutTemplate']);
    
  }
  
}

?>