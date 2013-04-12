<?php

ini_set('display_errors', 1);

require_once __DIR__ . '/simplehtmldom/simple_html_dom.php';

$scraper = new simple_html_dom();
$cinemacity_ids = array( 
	array( 'Mall Sofia', 'ms', '1261' ),
	array( 'Paradise Center', 'pc', '1266' )
);

for($i=0; $i<7; $i++){
	$weekdays[] = date("Y")."-".date("m")."-".(date("d")+$i);
}

?>
