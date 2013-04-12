<?php
	//!# /usr/bin/php
	// ini_set('display_errors', 1);

	include 'simplehtmldom/simple_html_dom.php';

	$scraper = new simple_html_dom();
	$cinemacity_ids = array( 
		array( 'ms', '1261' ),	// Mall Sofia
		array( 'pc', '1266' )	// Paradise Center
	);

	for($i=0; $i<7; $i++){
		$weekdays[] = date("Y")."-".date("m")."-".(date("d")+$i);
	}

	foreach( $weekdays as $day ) {
		foreach( $cinemacity_ids as $c ) {
			$scraper->load_file('http://demo5.sbnd.net/cinemacity/index.php?site_id='.$c[1].'&c_date='.$day); // Mall Sofia
			$scraper->save('cache/'.$c[0].'-'.$day.'.html');
			echo 'Created file at: cache/'.$c[0].'-'.$day.".html \n";
		}
	}

?>
