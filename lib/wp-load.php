<?php
	// those will simulate a browser HTTP/AJAX request.
	define('DOING_AJAX', true);
	define('WP_USE_THEMES', false);
	$_SERVER = array(
		'HTTP_HOST' => DOMAIN,
		'SERVER_NAME' => DOMAIN,
		'REQUEST_URI' => '/',
		'REQUEST_METHOD' => 'GET'
		);
	require (PATH . "/wp-load.php") ;
?>