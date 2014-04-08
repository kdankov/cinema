<?php
#!/usr/bin/php

require_once __DIR__ . '/_scraper.php';

echo "\n \n";
echo "Generating daly data for all cinemas. \n \n";

foreach( $cinemacity_ids as $cinema ) {

	$url = 'http://cinemacity.bg/en/scheduleInfo?locationId='.$cinema['lid'].'&date='.$dates[0].'&hideSite=1';
	$json =	__DIR__ . '/cache/cinemacity/daily/'.$cinema['lid'].'-'.$weekdays[0].'.json';
	$html =	__DIR__ . '/cache/cinemacity/daily/'.$cinema['lid'].'-'.$weekdays[0].'.html';

	parseCinemaCity( $url, $json, $html );

}

?>
