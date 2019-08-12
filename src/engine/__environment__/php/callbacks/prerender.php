<?php

// Locate the indexed routes, and read them.
$routes = ROUTES;

// Exit if no routes were found.
if( !$routes or empty($routes) ) exit();

// Prioritize routes by endpoint.
$prioritized = [];

// Prioritize the routes by forcing pages deeper within the site to be renderer last.
foreach( $routes as $route ) {

  // Get the route's endpoint broken down into its path parts.
  $endpoint = explode('/', trim((is_array($route['endpoint']) ? $route['endpoint'][1] : $route['endpoint']), '/'));

  // Determine the priority level.
  $level = $endpoint[count($endpoint) - 1] == 'index' ? 0 : count($endpoint);

  // Initialize the prioritized level if it doesn't already exist.
  if( !isset($prioritized[$level]) ) $prioritized[$level] = [];

  // Prioritize the route based on its priority level.
  $prioritized[$level][] = $route;

}

// Make sure the prioritized items are in priority order.
ksort($prioritized, SORT_NUMERIC);

// Initialize curl.
$curl = curl_init();

// Initialize a set of curl responses.
$responses = [];

// Prerender routes based on their priority order.
foreach( $prioritized as $routes ) {

  // Build and execute curl requests for each route to force it to prerender.
  foreach( $routes as $i => $route ) {

    // Get the route's endpoint and URL.
    $endpoint = is_array($route['endpoint']) ? $route['endpoint'][1] : $route['endpoint'];
    $url = is_array($route['url']) ? $route['url'][1] : $route['url'];

    // Configure the curl request.
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);

    // Execute the curl request, and get the response.
    $response = curl_exec($curl);

    // Save the curl response.
    $responses[$i] = [
      'url' => $url,
      'endpoint' => $route['endpoint'],
      'response' => trim($response)
    ];

  }

}

// Return the responses.
return $responses;

?>
