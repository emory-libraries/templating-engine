<?php

namespace HandlebarsHelpers;

trait CodeHelpers {
  
  // Embed code from an external data file as preformatted text.
  // FIXME: Because an `embed` helper also exists within the `HandlebarsLayouts` helpers, this gets overwritten.
  public static function embed( $path, $language ) {
    
    // Get extension from file when not give.
    if( is_array($language) ) $language = pathinfo($path, PATHINFO_EXTENSION);
    
    // Initialize the result.
    $code = ''; 
    
    // Verify that the file exists.
    if( file_exists(CONFIG['data']['site']['root'].'/'.$path) ) {
    
      // Get the code.
      $code = file_get_contents(CONFIG['data']['site']['root'].'/'.$path);
      
      // Handle extensions.
      switch( $language ) {
          
        case 'markdown':
        case 'md':
          
          $language = 'markdown';
          $code = implode('&#x60', explode('`', $code));
          
          break;
          
      }
      
    }
    
    // Return the code block.
    return "```{$language}\n".trim($code)."\n```\n";
    
    
  }
  
  // Embed a GitHub Gist using only the ID of the Gist.
  public static function gist( $id ) {
    
    // Embed Gist.
    return (new HTML('script', [
      'src' => "https://gist.github.com/{$id}.js"
    ]))->html();
    
  }
  
  // Generate the HTML for a jsFiddle link with the given `params`.
  public static function jsfiddle( $options ) {
    
    // Get attributes.
    $attrs = array_get($options, 'hash');
    
    // Require an ID.
    if( !isset($attrs['id']) ) throw new Error('jsfiddle helper expects an `id`');
    
    // Set attributes.
    $attrs['id'] = "http://jsfiddle.net/{$attrs['id']}";
    $attrs['width'] = isset($attrs['width']) ? $attrs['width'] : '100%';
    $attrs['height'] = isset($attrs['height']) ? $attrs['height'] : '300';
    $attrs['skin'] = isset($attrs['skin']) ? $attrs['skin'] : '/light/';
    $attrs['tabs'] = (isset($attrs['tabs']) ? ($attrs['tabs'] === true ? 'result,js,html,css' : $attrs['tabs']) : 'result,js,html,css').$attrs['skin'];
    $attrs['src'] = $attrs['id'].'/embedded/'.$attrs['tabs'];
    $attrs['allowfullscreen'] = isset($attrs['allowfullscreen']) ? $attrs['allowfullscreen'] : 'allowfullscreen';
    $attrs['frameborder'] = isset($attrs['frameborder']) ? $attrs['frameborder'] : '0';
    
    // Unset attributes.
    unset( $attrs['tabs'] );
    unset( $attrs['skin'] );
    unset( $attrs['id'] );
    
    // Embed jsFiddle.
    return (new HTML('iframe', $attrs))->html();
    
  }
  
}

?>