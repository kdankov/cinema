<?php
#!/usr/bin/php

require_once __DIR__ . '/_scraper.php';

echo "\n \n";
echo "Generating weekly data for all cinemas. \n \n";

$count = 0;

foreach( $weekdays as $weekday ) {

	foreach( $cinemacity_ids as $cinema ) {
		$url = 'http://cinemacity.bg/en/scheduleInfo?locationId='.$cinema['lid'].'&date='.$dates[$count].'&hideSite=1';
		$json =	__DIR__ . '/cache/cinemacity/weekly/'.$cinema['lid'].'-'.$weekday.'.json';
		$html =	__DIR__ . '/cache/cinemacity/weekly/'.$cinema['lid'].'-'.$weekday.'.html';
		parseCinemaCity( $url, $json, $html );
	}

	$count++;
}

?>
