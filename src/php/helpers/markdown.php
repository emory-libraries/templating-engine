<?php

return function( $template ) {
  
  // Get context.
  $context = is_array($template) ? $template['_this'] : $options['_this'];
  
  // Initialize the markdown engine.
  $markdown = new Markdown(array_get($context, '__config__.markdown'));
  
  // Initialize the mustache engine.
  $mustache = new Mustache_Engine();
  
  // Render any mustache.
  $rendered = $mustache->render((is_array($template) ? $template['fn']() : $template), $context);
  
  // Render the markdown.
  return $markdown->text($rendered);
  
};

?>