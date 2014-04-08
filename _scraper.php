<?php

ini_set('display_errors', 1);

require_once __DIR__ . '/_simplehtmldom/simple_html_dom.php';

$weekdays = array();
$dates = array();

$link = 'http://195.117.18.73/ReservationsBG?key=Sofia&ec=$PrsntCode$';

$cinemacity_ids = array( 
	array( 'cinema' => 'Mall Sofia',			'lid' => '1010605', 'vtype' => '0', 'key' => 'Sofia' ),
	array( 'cinema' => 'Mall Sofia (IMAX)',		'lid' => '1010605', 'vtype' => '2', 'key' => 'Sofia' ),
	array( 'cinema' => 'Burgas',				'lid' => '1010601', 'vtype' => '1', 'key' => 'BGBurgas' ),
	array( 'cinema' => 'Paradise Center',		'lid' => '1010602', 'vtype' => '0', 'key' => 'BGParadise' ),
	array( 'cinema' => 'Paradise Center (4DX)', 'lid' => '1010602', 'vtype' => '5', 'key' => 'BGParadise' ),
	array( 'cinema' => 'Plovdiv',				'lid' => '1010603', 'vtype' => '1', 'key' => 'plovdiv' ),
	array( 'cinema' => 'Rousse',				'lid' => '1010604', 'vtype' => '1', 'key' => 'BGRuse' ),
	array( 'cinema' => 'Stara Zagora',			'lid' => '1010606', 'vtype' => '1', 'key' => 'BGZagora' )
);

for($i=0; $i<7; $i++){
	$weekdays[] = date("Y")."-".date("m")."-".date("d",strtotime('+'.$i.' day'));
}

for($i=0; $i<7; $i++){
	$dates[] = date("d",strtotime('+'.$i.' day'))."/".date("m")."/".date("Y");
}

function parseCinemaCity($url, $json, $html){
	
	$scraper = new simple_html_dom();
	$scraper->load_file($html);
	//$scraper->save($html);

	$movies = array();

	foreach( $scraper->find('td[class="featureName"]') as $entry) {

		$screenings = array();

		$rating = $entry->next_sibling()->plaintext;
		$language = $entry->next_sibling()->next_sibling()->next_sibling()->plaintext;
		$duration = $entry->next_sibling()->next_sibling()->next_sibling()->next_sibling()->plaintext;

		$movieName = $entry->first_child()->plaintext;
		$infoLink = $entry->first_child()->attr["href"];
		$featureCode = $entry->first_child()->attr["data-feature_code"];

		$is2D = false;
		$is3D = false;
		$isA = false;
		$isB = false;
		$isC = false;
		$isD = false;
		$isIMAX = false;

		$movieName = str_ireplace(" (A)",	"", $movieName, $isA);
		$movieName = str_ireplace(" (А)",	"", $movieName, $isA);
		$movieName = str_ireplace(" (В)",	"", $movieName, $isB);
		$movieName = str_ireplace(" (B)",	"", $movieName, $isB);
		$movieName = str_ireplace(" (C)",	"", $movieName, $isC);
		$movieName = str_ireplace(" (С)",	"", $movieName, $isC);
		$movieName = str_ireplace(" (D)",	"", $movieName, $isD);

		$movieName = str_ireplace(" 2D",	"", $movieName, $is2D);
		$movieName = str_ireplace(" 3D",	"", $movieName, $is3D);
		$movieName = str_ireplace(" IMAX",	"", $movieName, $isIMAX);

		foreach( $entry->parent()->find('td[class="prsnt"] a') as $screening ) {
			if( $screening->innertext ) {

				$screenings += array(
					$screening->innertext => $screening->attr["data-prsnt_code"]
				);

			}
		}

		$movies[] = array(
			'title-en' => $movieName,
			'info-link' => $infoLink,
			'feature-code' => $featureCode,
			'language' => $language,
			'rating-en' => $rating,
			'duration' => $duration,
			'3D' => $is3D,
			'A' => $isA,
			'B' => $isB,
			'C' => $isC,
			'D' => $isD,
			'screenings' => $screenings
		);
	}

	$fp = fopen($json, 'w');
	fwrite($fp, json_encode($movies));
	fclose($fp);

	echo 'Created file at:' . $json . "\n";
	//echo 'Created file at:' . $html . "\n\n";
}

?>
