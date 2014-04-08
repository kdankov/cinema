<?php
#!/usr/bin/php

require_once __DIR__ . '/scraper.php';

$scraper->load_file('http://www.programata.bg/?p=30&l=1&c=1');
$json =	__DIR__ . '/cache/programata/movies.json';

$movies = array();
$screenings = array();

foreach( $scraper->find('ul[class=photo-list] li h2 a') as $entry) {

	$movieNameBG = $entry->find('span' , 0)->plaintext;
	$movieNameEN = $entry->find('em', 0)->plaintext;

	$link = $entry->href;

	$movies[] = array(
		'title_en' => $movieNameEN,
		'title_bg' => $movieNameBG,
		'link' => 'http://programata.bg/' . $link
	);
}

$fp = fopen($json, 'w');
fwrite($fp, json_encode($movies));
fclose($fp);

echo 'Created file at:' . $json . "\n";

?>

