<?php

/*
 * Failure
 *
 * Handles fail points within the templating engine.
 */
class Failure extends Error {
  
  // Construct the error normally.
  function __construct( int $code ) {
    
    // Call the parent constructor.
    parent::__construct('Templating Engine Error', $code);
    
  }
  
  // Get the error page in lieu of an error message.
  function getErrorPage() {
    
    // Get the error code, message, and trace.
    $code = $this->getCode();
    $message = $this->getMessage();
    $stack = $this->getTraceAsString();
    
    // Get the error data.
    $error = CONFIG['errors'][$code];

    // Simulate an error route.
    $route = new Route([
      'id' => $code,
      'error' => true,
      'endpoint' => '/'.$code,
      'cache' => CONFIG['engine']['cache']['pages'].'/'.$code.'.php'
    ]);

    // Simulate some error data.
    $data = new Data([
      'data' => array_merge($error, [
        'code' => $code,
        'trace' => [
          'message' => $message,
          'stack' => $stack
        ]
      ])
    ]);

    // Get all pattern templates.
    $templates = array_reduce(Index::scan(PATTERN_GROUPS['templates']), function($result, $template) {

      // Map the template by its ID and PLID.
      $result[Pattern::plid($template)] = $result[Pattern::id($template)] = $template;

      // Continue mapping templates by ID and PLID.
      return $result;

    }, []);

    // Get the default error pattern.
    $default = CONFIG['defaults']['errorTemplate'].(DEVELOPMENT ? CONFIG['defaults']['traceTemplate'] : '');

    // Simulate an error pattern.
    $pattern = isset($error['template']) ? new Pattern([
      'pattern' => array_get($templates, $data['template'], $default),
      'template' => $error['template'],
      'pageType' => null
    ]) : new Pattern([
      'pattern' => $default,
      'template' => null
    ]);

    // Simulate an error endpoint.
    $endpoint = new Endpoint($route, $data, $pattern);

    // Render the error page.
    return Renderer::error($endpoint, false);
    
  }
  
}

?>