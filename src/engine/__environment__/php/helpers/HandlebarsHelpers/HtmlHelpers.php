<?php

namespace HandlebarsHelpers;

trait HtmlHelpers {
  
  // Stringify attributes in the options hash.
  public static function attr( $options ) {
    
    return trim(array_to_attr(array_get($options, 'hash', []))); 
    
  }
  
  // Add an array of `<link>` tags, where relative paths are automatically resolved using `options.assets` or our global configurations.
  public static function css( array $stylesheets, $options ) {
    
    // Get arguments.
    $arguments = func_get_args();
    $options = array_last($arguments);
    $stylesheets = func_num_args() < 2 ? array_get($options, 'hash.href', []) : $stylesheets;
    
    // Force stylesheets into a list.
    $stylesheets = is_array($stylesheets) ? $stylesheets : [$stylesheets];
    
    // Get assets path.
    $assets = array_get($options, 'assets', false) ? array_get($options, 'assets').'/css' : (isset(CONFIG['engine']['css']) ? CONFIG['engine']['css'] : ''); 
    
    // Return a link tag for each stylesheet.
    return implode("\n", array_map(function($stylesheet) use ($assets) {
      
      // Get the stylesheet's extension.
      $extension = pathinfo($stylesheet, PATHINFO_EXTENSION);
      $href = $stylesheet;
      
      // Use the assets path as the base path for non-URLs.
      if( !preg_match('/^\/\/|\:\/\//', $stylesheet) ) $href = $assets === '' ? "{$assets}{$href}" : "{$assets}/{$href}";
      
      // Initialize the link element.
      $element = new HTML('link', [
        'type' => 'text/css',
        'rel' => 'stylesheet',
        'href' => $href
      ]);
      
      // Change rel for non-CSS files.
      switch( $extension ) {
        case 'less': $element->attr('rel', 'stylesheet/less'); break;
      }
      
      // Return the element's markup.
      return $element->html();
      
    }, $stylesheets));
    
  }
  
  // Add an array of `<script>` tags, where relative paths are automatically resolved using `options.assets` or our global configurations.
  public static function js( array $scripts, $options ) {
    
    // Get arguments.
    $arguments = func_get_args();
    $options = array_last($arguments);
    $scripts = func_num_args() < 2 ? array_get($options, 'hash.href', []) : $scripts;
    
    // Force scripts into a list.
    $scripts = is_array($scripts) ? $scripts : [$scripts];
    
    // Get assets path.
    $assets = array_get($options, 'assets', false) ? array_get($options, 'assets').'/js' : (isset(CONFIG['engine']['js']) ? CONFIG['engine']['js'] : ''); 
    
    // Return a script tag for each script.
    return implode("\n", array_map(function($script) use ($assets) {
      
      // Get the stylesheet's extension.
      $extension = pathinfo($script, PATHINFO_EXTENSION);
      $src = $script;
      
      // Use the assets path as the base path for non-URLs.
      if( !preg_match('/^\/\/|\:\/\//', $script) ) $src = $assets === '' ? "{$assets}{$src}" : "{$assets}/{$src}";
      
      // Initialize the link element.
      $element = new HTML('script', [
        'src' => $src
      ]);
      
      // Add type for non-JavaScript files.
      switch( $extension ) {
        case 'coffee': $element->attr('type', 'text/coffeescript'); break;
      }
      
      // Return the element's markup.
      return $element->html();
      
    }, $scripts));
    
  }
  
  // Strip HTML tags from a string, so that only the text nodes are preserved.
  public static function sanitize( $string ) {
    
    return trim(strip_tags($string));
    
  }
  
  // Block helper for creating unordered lists (`<ul></ul>`).
  public static function ul( $context, $options ) {
    
    // Get attributes.
    $attributes = array_get($options, 'hash', []);
    
    // Initialize the list.
    $list = new HTML('ul', $attributes); 
    
    // Add list items to the list.
    foreach( $context as $item ) { 
      
      // Render any subcontexts.
      if( !is_string($item) ) $item = $options['fn']($item);
      
      // Create the list item element and add it to the list.
      (new HTML('li', [
        'text' => $item
      ]))->appendTo($list);
      
    }
    
    // Return the list.
    return $list->html();
    
  }
  
  // Block helper for creating ordered lists (`<ol></ol>`).
  public static function ol( $context, $options ) {
    
    // Get attributes.
    $attributes = array_get($options, 'hash', []);
    
    // Initialize the list.
    $list = new HTML('ol', $attributes); 
    
    // Add list items to the list.
    foreach( $context as $item ) { 
      
      // Render any subcontexts.
      if( !is_string($item) ) $item = $options['fn']($item);
      
      // Create the list item element and add it to the list.
      (new HTML('li', [
        'text' => $item
      ]))->appendTo($list);
      
    }
    
    // Return the list.
    return $list->html();
    
  }
  
  // Returns a `<figure>` with a thumbnail linked to a full picture.
  public static function thumbnailImage( $context ) {
    
    // Initialize the figure, image, and link elements.
    $figure = new HTML('figure', [
      'id' => array_get($context, 'id', false) ? 'image-'.$context['id'] : null,
      'class' => implode(' ', array_get($context, 'classes.figure', []))
    ]);
    $image = new HTML('img', [
      'src' => $context['thumbnail'],
      'alt' => array_get($context, 'alt'),
      'width' => array_get($context, 'size.width'),
      'height' => array_get($context, 'size.height'),
      'class' => implode(' ', array_get($context, 'classes.image', []))
    ]);
    $link = new HTML('a', [
      'href' => array_get($context, 'full', $context['thumbnail']),
      'class' => implode(' ', array_get($context, 'classes.link', []))
    ]);
    
    // Add the image to the link.
    $link->append($image);
    
    // Add the link to the figure.
    $figure->append($link);
    
    // Optionally, add a caption to the figure if given.
    if( array_get($context, 'caption', false) ) (new HTML('figcaption', [
      'text' => $context['caption']
    ]))->appendTo($figure);
    
    // Return the figure's markup.
    return $figure->html();
      
  }
  
}

?>