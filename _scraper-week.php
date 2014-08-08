<?php
#!/usr/bin/php

require_once('_scraper.php');

echo "\n \n";
echo "Generating weekly data for all cinemas. \n \n";

$count = 0;

foreach( $weekdays as $weekday ) {

  foreach( $cinemacity_ids as $cinema ) {
    $url = 'http://cinemacity.bg/en/scheduleInfo?locationId='.$cinema['lid'].'&date='.$dates[$count].'&hideSite=1&venueTypeId='.$cinema['vtype'];
    $html =	__DIR__ . '/cache/cinemacity/'.$cinema['lid'].'-'.$cinema['vtype'].'-'.$weekday.'.html';
    $json =	__DIR__ . '/cache/local/'.$cinema['lid'].'-'.$cinema['vtype'].'-'.$weekday.'.json';
    parseCinemaCity( $url, $json, $html );
  }

  $count++;
}

?>
