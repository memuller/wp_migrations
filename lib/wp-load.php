<?php
	// those will simulate a browser HTTP/AJAX request.
	define('DOING_AJAX', true);
	define('WP_USE_THEMES', false);
	$_SERVER = array(
		'HTTP_HOST' => $domain,
		'SERVER_NAME' => $domain,
		'REQUEST_URI' => '/',
		'REQUEST_METHOD' => 'GET'
		);
	require ($path . "/wp-load.php") ;
?>