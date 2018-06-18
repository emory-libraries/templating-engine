<?php

return function( $template ) {
  
  // Initialize the markdown engine.
  $markdown = new Parsedown();
  
  // Render the markdown.
  return $markdown->text( (is_array($template) ? $template['fn']() : $template) );
  
};

?>