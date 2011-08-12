<?php 
	require_once 'lib/utils.php' ;
	require_once 'lob/wp-load.php';

	$max_blog_id = $wpdb->get_var('select max(blog_id) from wp_blogs');
	echo $max_blog_id ;
	
 ?>