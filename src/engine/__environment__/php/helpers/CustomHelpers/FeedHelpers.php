<?php

namespace CustomHelpers;

trait FeedHelpers {

  // Fetch and parse a feed from a given URL.
  public static function fetchFeed( string $url ) {

    // Get the feed.
    $feed = new Feed($url);

    // Get the feed data.
    $data = $feed->data;

    // Verify that the feed was valid and some data was fetched.
    if( !isset($data) or !is_array($data) ) return;

    // Merge feed metadata into the feed.
    $data['__meta__'] = [
      'type' => $feed->type,
      'version' => $feed->version
    ];

    // Return the feed data.
    return $data;

  }

}

?>
