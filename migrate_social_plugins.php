<?php 
	define('PATH', '/var/www/html/blog.cn') ;
	define('DOMAIN', 'blogcn.local') ;
	require('lib/utils.php') ;
	require('lib/wp-load.php') ;

	message("Loading sociable options from blog 1...") ;
	switch_to_blog(1) ;
	
	$global_options = get_option('sociable_options');
	$global_sites = get_option('sociable_known_sites') ;
	$global_helpus = 1;

	done();
	
	message("Looping on each blog:") ;
	$blog_ids = $wpdb->get_col("select blog_id from $wpdb->blogs") ;
	foreach ($blog_ids as $blog_id) {
		message("* Blog $blog_id : ", false) ;

		$success = update_blog_option($blog_id, 'sociable_options', $global_options) ;

		$success = $success && update_blog_option($blog_id, 'sociable_known_sites', $global_sites) ;

		$success = $success && update_blog_option($blog_id, 'sociable_helpus', $global_helpus) ;

		step($success ? 'options loaded... ' : 'failed to load options... ') ;

		done() ;
	}

	done();
 ?>