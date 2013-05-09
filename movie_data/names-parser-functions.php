<?php
/*
 * Lyubomir Popov https://github.com/lpopov/
 */


setlocale(LC_ALL, 'en_US.UTF8');

require_once __DIR__ . '/../simplehtmldom/simple_html_dom.php';


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


function cleanString($string){
    $string = preg_replace('/[^\pL0-9]+/u','', $string);
    $string = clearUTF($string);
    return $string;
}


// http://php.net/manual/en/function.iconv.php#83238
function clearUTF($s)
{
    $r = '';
    $s1 = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
   
    for ($i = 0; $i < strlen($s1); $i++)
    {
        $ch1 = $s1[$i];
        $ch2 = mb_substr($s, $i, 1);

        $r .= $ch1=='?'?$ch2:$ch1;
    }

    return $r;
}

function removePunctuation($string){
    $string = preg_replace('/[\pP+]+/u','', $string);
    return $string;
}


function addInfoLinks($filename){
    try{
        $dbFilename = __DIR__ . '/movie_data.db';
        if(!file_exists($dbFilename)){
            echo "Movie database does not exist!\n";
            return false;
        }
        $db = new SQLite3($dbFilename);
        
        $scraper = new simple_html_dom();
        $scraper->load_file($filename);
        foreach ($scraper->find('//td[class="movie_name"]') as $tdMovieName){
            $movieName = $tdMovieName->plaintext;
            $movieName = str_ireplace("(A)", "", $movieName);
            $movieName = str_ireplace("(А)", "", $movieName);
            $movieName = str_ireplace("(В)", "", $movieName);
            $movieName = str_ireplace("(B)", "", $movieName);
            $movieName = str_ireplace("(C)", "", $movieName);
            $movieName = str_ireplace("(С)", "", $movieName);
            $movieName = str_ireplace("(D)", "", $movieName);
            $movieName = str_ireplace("3D", "", $movieName);
            $movieName = str_ireplace("IMAX", "", $movieName);
            
            $findStmt = $db->prepare('
                SELECT imdb_url, programata_url FROM movie_data WHERE bg_title_clean = :bg_title_clean
            ');
            $findStmt->bindValue(':bg_title_clean', cleanString($movieName), SQLITE3_TEXT);

            $result = $findStmt->execute();
            $movieData = $result->fetchArray();

            if(empty($movieData)){
                echo $movieName. " not found\n";
            }else{
                $tdMovieName->innertext = $tdMovieName->plaintext;
                $tdMovieName->innertext .= " <a href='".$movieData['programata_url']."' target='_blank'><em>програмата</em></a>";
                $tdMovieName->innertext .= " <a href='".$movieData['imdb_url']."' target='_blank'><em>imdb</em></a>";
            }
            
        }
        
        $scraper->save($filename);

        
    }catch(Exception $e){
        echo "Error adding links - ".$e->getMessage();
        return false;
    }
    return true;
}

function saveUrlToFile($url, $filename){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    
    $result = curl_exec($ch);
    
    file_put_contents($filename, $result);
    
    curl_close($ch);
    
    return true;
}