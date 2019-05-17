<?php

namespace HandlebarsHelpers;

use Markdown;
use Mustache_Engine;

trait MarkdownHelpers {
  
  // Inline and block helper that converts a string of markdown to HTML. [aliased as md]
  public static function markdown( $template, $options = null ) {
    
    // Swap template and options for block contexts.
    if( is_array($template) and is_null($options) ) {
      
      $options = $template;
      $template = null;

    }
    
    // Get the context.
    $context = $options['_this'];  
    
    // Get markdown engine settings.
    $settings = array_merge((isset(CONFIG['markdown']) ? CONFIG['markdown'] : []), array_get($options, 'hash', []));
    
    // Initialize the markdown and mustache engines.
    $markdown = new Markdown($settings);
    $mustache = new Mustache_Engine();

    // Render any mustache.
    $rendered = $mustache->render((isset($options['fn']) ? $options['fn']() : $template), $context);

    // Render the markdown.
    return $markdown->text($rendered);
  
  }
  
  // Inline and block helper that converts a string of markdown to HTML. [alias for markdown]
  public static function md( $template, $options = null ) {
    
    return forward_static_call('HandlebarsHelpers\MarkdownHelpers::markdown', $template, $options);
    
  }
  
}

?>