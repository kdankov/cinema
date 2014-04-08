<?php

ini_set('display_errors', 1);

require_once __DIR__ . '/simplehtmldom/simple_html_dom.php';
//require_once __DIR__ . '/movie_data/names-parser-functions.php';

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

function parseCinemaCity($url, $json){
	
	$scraper = new simple_html_dom();
	$scraper->load_file($url);

	$movies = array();
	$screenings = array();

	foreach( $scraper->find('td[class="movie_name"]') as $entry) {
		$tr = $entry->parent();

		foreach( $tr->find('td a') as $screening) {
			if( $screening->innertext ) {
				$screenings += array(
					$screening->innertext => $screening->href
				);
			}
		}

		$movieName = $entry->plaintext;
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

		$movies[] = array(
			'title_bg' => $movieName,
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
}

/*
 * Contribution from:
 * Lyubomir Popov https://github.com/lpopov/
 */
function getMovieImdb($movieTitle, $movieYear){
    
    $movieTitleOrig = $movieTitle;
    $movieTitle = str_replace("3D", "", removePunctuation($movieTitle));
    
    $imdbScraper = new simple_html_dom();
    
    $cacheFile = __DIR__ . '/internal_cache/imdb_'.md5($movieTitle).'.html';
    
    if(file_exists($cacheFile)){
        $imdbScraper->load_file($cacheFile);
    }else{
        $imdbScraper->load_file('http://www.imdb.com/find?s=tt&q='.urlencode($movieTitle));
        $imdbScraper->save($cacheFile);
    }    
    
    $movieAltTitle1 = str_ireplace(" and ", " & ", $movieTitle);
    $movieAltTitle2 = str_ireplace(" & ", " and ", $movieTitle);

    $movieTitle = cleanString($movieTitle);
    $movieAltTitle1 = cleanString($movieAltTitle1);
    $movieAltTitle2 = cleanString($movieAltTitle2);
    
    // Sometimes the years in the Programata are wrong.
    // The first match with the wrong year is saved, but not returned, 
    // in case there is correct title/year pair later in the list
    $wrongYearData = array();
    $movieLastYear = (string)($movieYear - 1);
    
    foreach ($imdbScraper->find('//td[class="result_text"]') as $result){
        
        $resultTextOrig = trim($result->plaintext);
        $resultText = cleanString($result->plaintext);
        
        if(     (
                    stripos($resultTextOrig,'(TV Episode)') === FALSE &&
                    stripos($resultTextOrig,'(TV Series)') === FALSE
                ) && (
                    stripos($resultText,$movieTitle) !== FALSE ||
                    stripos($resultText,$movieAltTitle1) !== FALSE ||
                    stripos($resultText,$movieAltTitle2) !== FALSE 
                ) && ( 
                    stripos($resultText,$movieYear) !== FALSE ||
                    stripos($resultText,$movieLastYear) !== FALSE 
                ) )
        {
            $movieLink = $result->first_child();
            $movieData = array(
                'imdb_url'  =>  'http://imdb.com'.substr($movieLink->href, 0, strpos($movieLink->href,'?')),
                'title'     => $resultTextOrig
            );
            
            if(stripos($resultText,$movieYear) !== FALSE){
                return $movieData;
            }else{
                if(empty($wrongYearData))
                    $wrongYearData = $movieData;
            }
        }
        
        
    }
    
    if(!empty($wrongYearData))
        return $wrongYearData;
    return false;
}

?>
