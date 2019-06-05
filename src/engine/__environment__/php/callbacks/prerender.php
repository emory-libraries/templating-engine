<?php

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
  $endpoint = is_array($route->endpoint) ? $route->endpoint[1] : $route->endpoint;
  $url = is_array($route->url) ? $route->url[1] : $route->url;
  
  // Save the URL.
  $responses[$i] = ['url' => $url];
  
  // Initialize the request.
  $requests[$i] = curl_init();
  
  // Configure the request.
  curl_setopt($requests[$i], CURLOPT_URL, $url);
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
  
  // Initialize the responsei if not already initialized.
  if( !isset($responses[$i]) or !is_array($responses[$i]) ) $responses[$i] = [];

  // Save the response.
  $responses[$i]['endpoint'] = $routes['data'][$i]->endpoint;
  $responses[$i]['response'] = trim(curl_multi_getcontent($response));

  // Remove the request from the queue after its completed.
  curl_multi_remove_handle($curl, $response);

}

// Close curl.
curl_multi_close($curl);

// Output the responses.
if( DEBUGGING ) {
  
  // Capture the results.
  if( METHOD === 'POST' ) $output['prerender'] = $responses;
  
  // Otherwise, log the response to the command line.
  else d($responses);
  
}

?>