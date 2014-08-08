<?php

ini_set('display_errors', 1);

require_once('_simplehtmldom/simple_html_dom.php');

$weekdays = array();
$dates = array();

$link = 'http://195.117.18.73/ReservationsBG?key=Sofia&ec=$PrsntCode$';

$cinemacity_ids = array( 
  array( 'cinema' => 'Mall Sofia',			'lid' => '1010605', 'vtype' => '0', 'key' => 'Sofia' ),
  array( 'cinema' => 'Mall Sofia (IMAX)',		'lid' => '1010605', 'vtype' => '2', 'key' => 'Sofia' ),
  array( 'cinema' => 'Burgas',				'lid' => '1010601', 'vtype' => '1', 'key' => 'BGBurgas' ),
  array( 'cinema' => 'Paradise Center',		        'lid' => '1010602', 'vtype' => '0', 'key' => 'BGParadise' ),
  array( 'cinema' => 'Paradise Center (4DX)',           'lid' => '1010602', 'vtype' => '5', 'key' => 'BGParadise' ),
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

function grab_image($url, $saveto){
  if(!file_exists($saveto)){
    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $raw=curl_exec($ch);
    curl_close ($ch);

    $fp = fopen($saveto,'x');
    fwrite($fp, $raw);
    fclose($fp);

    echo 'Movie poster downloaded at:' . $saveto . "\n";
  } else {
    echo 'Image already exists. Skipping ... - ' . $saveto . "\n";
  }
}

function parseCinemaCity($url, $json, $html){
  
  echo '1. Start parser ... ' . "\n";

  $scraper = new simple_html_dom();

  if(!file_exists($html)){
    $scraper->load_file($url);
    $scraper->save($html);
    echo '1.1. HTML file downloaded from ' . $url . ' and created at:' . $html . "\n";
    
  }
  
  $scraper->load_file($html);
  echo '1.2. HTML file loaded: ' . $html . "\n";

  $movies = array();

  foreach( $scraper->find('td[class="featureName"]') as $entry) {

    $screenings = array();

    $rating   = $entry->next_sibling()->plaintext;
    $type     = $entry->next_sibling()->next_sibling()->plaintext;
    $language = $entry->next_sibling()->next_sibling()->next_sibling()->plaintext;
    $duration = $entry->next_sibling()->next_sibling()->next_sibling()->next_sibling()->plaintext;

    $movieName = $entry->first_child()->plaintext;
    //$infoLink = $entry->first_child()->attr["href"];
    $featureCode = $entry->first_child()->attr["data-feature_code"];

    $movieinfo_url      = 'http://cinemacity.bg/en/featureInfo?featureCode='.$featureCode;
    $movieinfo_html     = __DIR__ . '/cache/movies/'.$featureCode.'.html';
    $movieinfo_poster   = __DIR__ . '/cache/movies/posters/'.$featureCode.'.jpg';

    $movie_scraper = new simple_html_dom();
    echo '2. Getting movie information HTML.' . "\n";

    if(!file_exists($movieinfo_html)){
      $movie_scraper->load_file($movieinfo_url);
      $movie_scraper->save($movieinfo_html);

      echo '2.1. HTML file downloaded from ' . $movieinfo_url . ' and created at:' . $movieinfo_html . "\n";
    }

    $movie_scraper->load_file($movieinfo_html);
    echo '2.2. HTML file loaded: ' . $movieinfo_html . "\n";

    $poster = $movie_scraper->find('div[class="poster_holder"] img', 0)->src; 
    $synopsis = $movie_scraper->find('div[class="feature_info_synopsis"]', 0)->plaintext; 
    //var_dump($synopsis);
    //var_dump($movieinfo_posters);
    grab_image( $poster, $movieinfo_poster );

    $is2D   = false;
    $is3D   = false;
    $isA    = false;
    $isB    = false;
    $isC    = false;
    $isD    = false;
    $isIMAX = false;
    $is4DX  = false;

    $movieName = str_ireplace(" (A)",	"", $movieName, $isA);
    $movieName = str_ireplace(" (B)",	"", $movieName, $isB);
    $movieName = str_ireplace(" (С)",	"", $movieName, $isC);
    $movieName = str_ireplace(" (D)",	"", $movieName, $isD);
    $movieName = str_ireplace(" 4DX",	"", $movieName, $is4DX);

    $movieName = str_ireplace(" 2D",	"", $movieName, $is2D);
    $movieName = str_ireplace(" 3D",	"", $movieName, $is3D);
    $movieName = str_ireplace(" IMAX",	"", $movieName, $isIMAX);

    $count = 0;

    foreach( $entry->parent()->find('td[class="prsnt"] a') as $screening ) {
      if( $screening->innertext ) {

        $screenings += array(
          $count => array(
            'time' => str_replace("<br/>4DX", '', str_replace("<br/>IMAX", '', $screening->innertext)),
            'code' => $screening->attr["data-prsnt_code"]
          )
        );

        $count++;

      }
    }

    $movies[] = array(
      'title'         => $movieName,
      'poster'        => $poster,
      'feature-code'  => $featureCode,
      'language'      => $language,
      'rating'        => $rating,
      'duration'      => $duration,
      'type'          => $type,
      '3D'            => $is3D,
      'IMAX'          => $isIMAX,
      '4DX'           => $is4DX,
      'A'             => $isA,
      'B'             => $isB,
      'C'             => $isC,
      'D'             => $isD,
      'synopsis'      => $synopsis,
      'screenings'    => $screenings
    );
  }

  $fp = fopen($json, 'w');
  $fwrite = fwrite($fp, json_encode($movies));
  fclose($fp);
  echo '3. Parsing done. Saving JSON file' . $json . "\n\n";

}

?>
