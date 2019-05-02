<?php

/*
 * Engine
 *
 * This is the core of the templating engine. It's responsible for processing
 * the request, passing it along to the router to determine how to handle it,
 * then finally rendering the page, redirecting, or erroring accordingly.
 */
class Engine {

  // Some data about the request.
  protected $request;

  // A router to handle page rendering, redirecting, and erroring.
  protected $router;

  // Constructor
  function __construct() {

    // Add benchmark point.
    if (DEBUG_ENABLED) Performance\Performance::point('Engine', true);

    // Get data about the request.
    $this->request = new Request();

    // Add benchmark point.
    if (DEBUG_ENABLED) Performance\Performance::point('Request processed.');

    // Pass the request to the router.
    $this->router = new Router($this->request);

    // Run the templating engine.
    $this->run();

    // Add benchmark point.
    if (DEBUG_ENABLED) Performance\Performance::finish('Engine');

  }

  // Parse the route.
  private function run() {

    // Attempt to load the requested endpoint.
    echo $this->router->render();

  }

}

?>
