<?php

namespace HandlebarsLogging;

use __ as _;

trait DebugHelpers {
  
  // Outputs a debug statement with the current context.
  public static function _debug( $value ) {
    
    $arguments = func_get_args();
    $options = _::last($arguments);
    $key = array_get(_::tail(_::initial($arguments)), 0, null);
    $context = $options['_this'];
    
    if( isset($value) and count($arguments) > 0 ) {
      
      $value = isset($key) ? array_get($context, $key) : $context;
      
      $result = implode("\n", array_map(function ($message) {
        
        return "console.debug($message);";
        
      }, [
        '"================================="',
        '"CONTEXT:"',
        'JSON.parse("'.addslashes(json_encode($context)).'")',
        '"VALUE:"',
        'JSON.parse("'.addslashes(json_encode($value)).'")',
        '"================================="'
      ]));
      
      return "<script>{$result}</script>";
      
    }
    
  }
  
  // Returns data object as MD codeblock, HTML, or JSON based on `type`.
  public static function _inspect( $context, $options ) {
    
    $value = json_encode($context, JSON_PRETTY_PRINT);
    $type = array_get($options, 'hash.type', 'html');
    
    switch( $type ) {
      case 'md': return "```json\n{$value}\n```";
      case 'html': return "<div class=\"highlight highlight-json\"><pre><code>$value</code></pre></div>";
      default: return $value;
    }
    
  }
  
}

?>