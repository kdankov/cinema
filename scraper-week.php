<?php
#!/usr/bin/php

require_once __DIR__ . '/scraper.php';

foreach( $weekdays as $weekdays[0] ) {
	foreach( $cinemacity_ids as $c ) {
		$scraper->load_file('http://demo5.sbnd.net/cinemacity/index.php?site_id='.$c[2].'&c_date='.$weekdays[0]); // Mall Sofia
		$scraper->save(__DIR__ . '/cache/'.$c[1].'-'.$weekdays[0].'.html');
		echo 'Created file at: cache/'.$c[1].'-'.$weekdays[0].".html \n";
        if(addInfoLinks(__DIR__ . '/cache/'.$c[1].'-'.$weekdays[0].'.html'))
            echo 'Added links to: cache/'.$c[1].'-'.$weekdays[0].".html \n";    
	}
}

?>
