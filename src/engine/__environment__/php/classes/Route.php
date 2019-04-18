<?php

/*
 * Route
 *
 * Interprets some route data given the path to a data file.
 */
class Route {
  
  // The path of the data file.
  public $path = null;
  
  // The route ID.
  public $id;
  
  // The site's environment.
  public $environment = CONFIG['__site__']['environment'];
  
  // The site ID.
  public $site = CONFIG['__site__']['site'];
  
  // The site's domain.
  public $domain = CONFIG['__site__']['domain'];
  
  // The route's endpoint within the site.
  public $endpoint;
  
  // Whether the route redirects, and if so, where it redirects to.
  public $redirect = false;
  
  // The route's template type.
  public $template = CONFIG['defaults']['template'];
  
  // Constructs the route.
  function __construct( $route ) {
    
    // Infer route data if a path is given.
    if( is_string($route) ) {
    
      // Save the data file path.
      $this->path = $route;

      // Get the data file's ID.
      $this->id = File::id($route);

      // Determine the endpoint that would map to the data file.
      $this->endpoint = File::endpoint($route);

      // Make the `index` keyword optional for index endpoints.
      if( $this->id == 'index' ) $this->endpoint = [
        preg_replace('/index$/', '', $this->endpoint),
        $this->endpoint
      ];

      // Get the data file's data.
      $data = new Data($route);

      // Determine if the route redirects, and if so, get the redirect path.
      if( array_get($data->data, 'redirect', false) ) $this->redirect = array_get($data->data, 'redirect');

      // Get the route's template, or use the default template.
      if( !$this->redirect and array_get($data->data, 'template', false) ) {

        // Get the template name from the data file.
        $name = array_get($data->data, 'template');

        // Lookup the position of the template name within list of known template names.
        $index = array_search($name, array_values(array_get(CONFIG, 'config.template')));

        // Lookup the template ID if the index exists, or use the default template otherwise.
        $template = $index !== false ? array_keys(array_get(CONFIG, "config.template"))[$index] : $this->template;

        // Save the template ID.
        $this->template = $template;

      }
      
    }
    
    // Otherwise, extract the route data if an array is given.
    else if( is_array($route) ) {
      
      // Get the route's ID.
      $this->id = array_get($route, 'id', File::id($route['endpoint']));
      
      // Get the route's endpoint.
      $this->endpoint = $route['endpoint'];
      
      // Make the `index` keyword optional for index endpoints.
      if( str_ends_with($this->endpoint, '/') ) $this->endpoint = [
        $this->endpoint,
        $this->endpoint.'index'
      ];
      else if( str_ends_with($this->endpoint, 'index') ) $this->endpoint = [
        preg_replace('/index$/', '', $this->endpoint),
        $this->endpoint
      ];
      
      // Determine if the route redirects, and if so, get the redirect path.
      if( array_get($route, 'redirect', false) ) $this->redirect = $route['redirect'];
      
      // Get the route's template.
      if( !$this->redirect ) $this->template = $route['template'];
      
    }
    
  }
  
}

?>