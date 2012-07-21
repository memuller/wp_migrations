<?php 
	
	require 'lib/utils.php' ;
	require 'lib/wp-config-parse.php' ;

	$path = __DIR__ . "/../.." ;
	$path = realpath($path);
	$wp_config = new WPConfig($path); $wp_config = $wp_config->options ;
	
	if( !isset($load_wp) or $load_wp == true){
		if(isset($wp_config['DOMAIN_CURRENT_SITE'])) {
			$domain = $wp_config['DOMAIN_CURRENT_SITE'] ;
		} else {
			$domain = $wp_config['SITE_URL'];
			if(!$domain) 
				trigger_error('SITE_URL is not set on wp-config. Please set it.');
		}
		require('lib/wp-load.php');
	}
		
		
 ?>