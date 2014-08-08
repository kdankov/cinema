<?php
#!/usr/bin/php

require_once('scraper.php');

echo "\n \n";
echo "Generating cinemas JSON object. \n \n";

$fp = fopen('js/cinemas.js', 'w');
fwrite($fp, json_encode($cinemacity_ids));
fclose($fp);

?>

