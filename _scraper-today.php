<?php
#!/usr/bin/php

require_once('_scraper.php');

echo "\n \n";
echo "Generating daly data for all cinemas. \n \n";

foreach( $cinemacity_ids as $cinema ) {

  $url = 'http://cinemacity.bg/en/scheduleInfo?locationId='.$cinema['lid'].'&date='.$dates[0].'&hideSite=1&venueTypeId='.$cinema['vtype'];
  $html =	__DIR__ . '/cache/cinemacity/'.$cinema['lid'].'-'.$cinema['vtype'].'-'.$weekdays[0].'.html';
  $json =	__DIR__ . '/cache/local/'.$cinema['lid'].'-'.$cinema['vtype'].'-'.$weekdays[0].'.json';

  parseCinemaCity( $url, $json, $html );
}

?>
