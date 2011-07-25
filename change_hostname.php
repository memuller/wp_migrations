<?php
/**
 * Migrates the database of a WP Network installation to a new hostname.
 *
 * This script will change the hostname of a WPMU database.
 * It requires $old_domain and $new_domain as parameters - which should
 * countain just the domain name (no http://, no leading slash, etc).
 *
 * Currently, it will:
 * ** change hostnames on the blogs table.
 * ** change hostnames on the wp_options table of each blog.
 *
 * It WILL fail in the following scenarios:
 * ** a non-network WP installation.
 * ** a network installation with multiple sites (untested).
 *
 * Please note that this will affect only the database; you will need
 * to change the wp-options file (and anything else) as needed to finish
 * the migration. Use {@link http://codex.wordpress.org/Changing_The_Site_URL}
 * as a reference.
 *
 * Can only be run on CLI. Be warry of execution time and memory limits.
 *
 * @package WP Migrations
 */

 /*
 	FIXME: also change wp_site when present.
 	TODO: support path changes
 	TODO: write changes to wp-config
 	TODO: modularize stuff
 */

	$old_domain = 'localhost';
	$new_domain = 'localhost';

	if($argv[1] && $argv[2]){
		$old_domain = $argv[1];
		$new_domain = $argv[2];
	}
	
	if($old_domain == $new_domain){
		die("Old and new domains are the same - please specify different ones.");
	}

	error_reporting(E_ALL);
	if (! defined('STDIN') ) {
		die() ; 
	}
	define('DOING_AJAX', true);
	define('WP_USE_THEMES', false);
	$_SERVER = array(
		'HTTP_HOST' => $old_domain,
		'SERVER_NAME' => $old_domain,
		'REQUEST_URI' => '/',
		'REQUEST_METHOD' => 'GET'
		);
	require_once( "../wp-load.php") ;

	echo "* Migrating WP Database to '$new_domain' hostname. \n" ;
	echo "** Changing domains on $wpdb->blogs ...";
	$wpdb->query($wpdb->prepare("update $wpdb->blogs set domain=%s", $new_domain ));
	echo "done. \n" ;
	echo "** Changing wp_options on all blogs... ";
	$blog_list = $wpdb->get_results("select * from $wpdb->blogs");
	foreach( $blog_list as $blog){
		$table_name = $wpdb->prefix . $blog->blog_id . '_options';
		$exists = $wpdb->get_var("show tables like '$table_name' ");
		if(! $exists)
			$table_name = $wpdb->options;
		$query = 
			"update $table_name set
				option_value = replace(
					option_value, '$old_domain', '$new_domain'
				)
				where option_value like '%$old_domain%'";
		if ($wpdb->query($query)){
			echo $blog->blog_id . ' ' ;
		}else{
			$wpdb->print_error();
		}
	}
	echo "done. \n" ;

 ?>