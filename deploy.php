<?php

	$debug = true;

	// Turn on php errors
	if($debug){ ini_set('display_errors', 1); }
	
	// Are we getting the 'payload' json data via POST request? If not it will terminate the scritp.
	try
	{
		$payload = json_decode($_POST['payload']);
	}
	catch(Exception $e)
	{
		if($debug) { echo 'No payload data'; }
		exit(0);
	}

	if($debug)
	{
		// Debug functionality. Log the contents of the $_POST and the $payload variables in files.
		file_put_contents('/home/lazy/public_html/cinema.logs/payload.txt', $_POST['payload'], FILE_APPEND);
		file_put_contents('/home/lazy/public_html/cinema.logs/github.txt', print_r($payload, TRUE), FILE_APPEND);
		
		// If we are in debug mode and opening this file from a browser wrap everything in pre tags.
		echo '<pre>';
	}

	// Save the current directory state & change to initlab home folder
	$dir = getcwd();
	chdir('/home/lazy/public_html');
	
	if ($payload->ref == 'refs/heads/master'){
		exec('./deploy_cinema.sh 2>&1', $output);
	}

	chdir($dir);

	if($debug)
	{
		// Show the result of the deploy scripts
		print_r($output);
	}

?>
