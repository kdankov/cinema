<?php

ini_set('display_errors', 1);

require_once __DIR__ . '/simplehtmldom/simple_html_dom.php';

$scraper = new simple_html_dom();
$cinemacity_ids = array( 
	array( 'Mall Sofia', 'ms', '1261' ),
	array( 'Paradise Center', 'pc', '1266' ),
	array( 'Stara Zagora', 'sz', '1263' ),
	array( 'Ruse', 'ru', '1264' ),
	array( 'Burgas', 'bu', '1265' ),
	array( 'Mall Plovdiv', 'mp', '1262' )
);

for($i=0; $i<7; $i++){
	$weekdays[] = date("Y")."-".date("m")."-".date("d",strtotime('+'.$i.' day'));
}

?>
