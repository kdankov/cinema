<?php
/*
 * Lyubomir Popov https://github.com/lpopov/
 */

ini_set('display_errors', 0);
setlocale(LC_ALL, 'en_US.UTF8');

require_once __DIR__ . '/../simplehtmldom/simple_html_dom.php';
require_once __DIR__ . '/names-parser-functions.php';

$scraper = new simple_html_dom();

$programataCacheFile = __DIR__ . '/internal_cache/programata-movies_'.date('Y-m-d').'.html';

if(file_exists($programataCacheFile)){
    $scraper->load_file($programataCacheFile);
}else{
    saveUrlToFile('http://www.programata.bg/?p=30&l=1&c=1', $programataCacheFile);
    if(file_exists($programataCacheFile)){
        $scraper->load_file($programataCacheFile);
    }else{
        die('Error getting data file!');
    }
}


try{
    $db = new SQLite3(__DIR__ . '/movie_data.db');

    $movieTableCreateSQL = "
        CREATE TABLE IF NOT EXISTS movie_data(
            movie_id INTEGER PRIMARY KEY ASC,
            bg_title TEXT,
            orig_title TEXT,
            year INTEGER,
            imdb_url TEXT,
            imdb_title TEXT,
            bg_title_clean TEXT,
            programata_url TEXT
        )
    ";
    $tableCreated = $db->exec($movieTableCreateSQL);
    if(!$tableCreated) throw new Exception ("Error creating movie_data table!");


    foreach ($scraper->find('//ul[class="photo-list"]/li/h2/a/span') as $span){
        $em = $span->next_sibling();
                
        $bgTitle = trim($span->plaintext);
        $origTitle = trim($em->plaintext);
        
        $programataLink = 'http://www.programata.bg/'.html_entity_decode($span->parent()->href);
        
        $yearStr = $span->parent()->parent()->next_sibling()->plaintext;

        $yearStrArr = explode(',', $yearStr,3);

        $movieYear = '';
        if(!empty($yearStrArr[1]))
            $movieYear = trim($yearStrArr[1]);

        $findStmt = $db->prepare('
            SELECT COUNT(*) FROM movie_data WHERE bg_title = :bg_title
        ');
        $findStmt->bindValue(':bg_title', $bgTitle, SQLITE3_TEXT);

        $result = $findStmt->execute();
        $movieExists = $result->fetchArray();

        if(empty($movieExists[0])){

            $movieData = array(
                'imdb_url'  =>  '',
                'title'     =>  ''
            );

            $searchTitle = $origTitle;
            if(empty($searchTitle))
                $searchTitle = $bgTitle;


            $movieDataNew = getMovieImdb($searchTitle, $movieYear);
            if($movieDataNew)
                $movieData = $movieDataNew;

            if(empty($movieData['imdb_url']))
                echo "No match for ".$bgTitle."\n";

            $insertStmt = $db->prepare('
                INSERT INTO movie_data (bg_title, orig_title, year, imdb_url, imdb_title, bg_title_clean, programata_url) 
                VALUES(:bg_title, :orig_title, :year, :imdb_url, :imdb_title, :bg_title_clean, :programata_url)
            ');
            $insertStmt->bindValue(':bg_title', $bgTitle, SQLITE3_TEXT);
            $insertStmt->bindValue(':orig_title', $origTitle, SQLITE3_TEXT);
            $insertStmt->bindValue(':year', $movieYear, SQLITE3_INTEGER);
            $insertStmt->bindValue(':imdb_url', $movieData['imdb_url'], SQLITE3_TEXT);
            $insertStmt->bindValue(':imdb_title', $movieData['title'], SQLITE3_TEXT);
            $bgTitle = str_replace("3D", "", $bgTitle);
            $insertStmt->bindValue(':bg_title_clean', cleanString($bgTitle), SQLITE3_TEXT);
            $insertStmt->bindValue(':programata_url', $programataLink, SQLITE3_TEXT);

            $insertStmt->execute();
        }

    }

}catch(Exception $e){
    echo $e->getMessage();
    die();
}
