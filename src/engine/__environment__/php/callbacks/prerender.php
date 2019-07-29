<?php

// Locate the indexed routes file, and read it.
$routes = (isset(PATHS['routes']) and file_exists(PATHS['routes'])) ? json_decode(file_get_contents(PATHS['routes']), true) : false;

// Exit if no routes were found.
if( !$routes or empty($routes) ) exit();

// Initialize curl.
$curl = curl_init();

// Initialize a set of curl responses.
$responses = [];

// Build and execute curl requests.
foreach( $routes['data'] as $i => $route ) {

  // Get the route's endpoint and URL.
  $endpoint = is_array($route['endpoint']) ? $route['endpoint'][1] : $route['endpoint'];
  $url = is_array($route['url']) ? $route['url'][1] : $route['url'];

  // Configure the curl request.
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_NOBODY, true);
  curl_setopt($curl, CURLOPT_HEADER, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  // Execute the curl request, and get the response.
  $response = curl_exec($curl);

  // Save the curl response.
  $responses[$i] = [
    'url' => $url,
    'endpoint' => $route['endpoint'],
    'response' => trim($response)
  ];

}

// Return the responses.
return $responses;

?>
