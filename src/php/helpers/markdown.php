<?php

return function( $template ) {
  
  // Initialize the markdown engine.
  $markdown = new Md();
  
  // Initialize the mustache engine.
  $mustache = new Mustache_Engine();
  
  // Get context.
  $context = is_array($template) ? $template['_this'] : $options['_this'];
  
  // Render any mustache.
  $rendered = $mustache->render((is_array($template) ? $template['fn']() : $template), $context);
  
  // Render the markdown.
  return $markdown->text($rendered);
  
};

?>