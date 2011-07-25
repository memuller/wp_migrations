<?php 
	
	function load_wp($domain){
		// those will simulate a browser HTTP/AJAX request.
		define('DOING_AJAX', true);
		define('WP_USE_THEMES', false);
		$_SERVER = array(
			'HTTP_HOST' => $domain,
			'SERVER_NAME' => $domain,
			'REQUEST_URI' => '/',
			'REQUEST_METHOD' => 'GET'
			);
		require_once("../wp-load.php") ;
	}

	void function message($text, $line_break = true){
		$break = $line_break ? "\n" : "" ;
		echo $text . $break ;
	}

	void function progress($text){
		message($text, false);
	}
 ?>