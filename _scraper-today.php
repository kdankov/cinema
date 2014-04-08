<?php
#!/usr/bin/php

require_once __DIR__ . '/_scraper.php';

echo "\n \n";
echo "Generating daly data for all cinemas. \n \n";

foreach( $cinemacity_ids as $c ) {
	$url = 'http://demo5.sbnd.net/cinemacity/index.php?site_id='.$c[2].'&c_date='.$weekdays[0];
	$json =	__DIR__ . '/cache/cinemacity/daily/'.$c[1].'-'.$weekdays[0].'.json';
	parseCinemaCity( $url, $json );
}

?>