<?php

// Defines the base URL for use in development mode.
define('DEV_BASE_URL', 'http://localhost/templating-engine/public');

// Locate the indexed routes file, and read it.
$routes = (include CONFIG['engine']['cache']['index'].'/routes.php');

// Initialize curl.
$curl = curl_multi_init();

// Initialize a set of curl requests and responses.
$requests = [];
$responses = [];

// Build curl requests.
foreach( $routes['data'] as $i => $route ) {
  
  // Get the route's endpoint and URL.
  $endpoint = is_array($route->endpoint) ? $route->endpoint[0] : $route->endpoint;
  $url = is_array($route->url) ? $route->url[0] : $route->url;
  
  // Get the URL of the route to be fetched.
  $fetch = DEVELOPMENT ? DEV_BASE_URL.'/'.CONFIG['__site__']['domain'].$endpoint : $url;
  
  // Initialize the request.
  $requests[$i] = curl_init();
  
  // Configure the request.
  curl_setopt($requests[$i], CURLOPT_URL, $fetch);
  curl_setopt($requests[$i], CURLOPT_NOBODY, true);
  curl_setopt($requests[$i], CURLOPT_HEADER, true);
  curl_setopt($requests[$i], CURLOPT_RETURNTRANSFER, true);
  
  // Queue the curl request.
  curl_multi_add_handle($curl, $requests[$i]);
  
}

// Initialize an index.
$index = null;

// Process all curl requests.
do { 
  
  // Execute all curl requests.
  curl_multi_exec($curl, $index);

}  while( $index > 0 );
  
// Capture the responses.
foreach($requests as $i => $response) {

  // Save the response.
  $responses[$i] = [
    'endpoint' => $routes['data'][$i]->endpoint,
    'response' => curl_multi_getcontent($response)
  ];

  // Remove the request from the queue after its completed.
  curl_multi_remove_handle($curl, $response);

}

// Close curl.
curl_multi_close($curl);

// Add benchmark point.
if( DEVELOPMENT ) Performance\Performance::point('Prerender callback completed.');

// Output the responses.
if( DEVELOPMENT ) {
  
  // Capture the results.
  if( $method === 'POST' ) $output['prerender'] = $responses;
  
  // Otherwise, log the response to the command line.
  else d($responses);
  
}

?>