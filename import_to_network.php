<?php
	require_once '../wp-migrations-config.php' ;
	require_once 'lib/utils.php' ;
	require_once 'lib/wp-load.php';

	require 'lib/ez_sql/shared/ez_sql_core.php' ;
	require 'lib/ez_sql/mysql/ez_sql_mysql.php' ;

	$single_db_name = $argv[1];
	$lock_tables = false ; 

	$single_db = new ezSQL_mysql(DB_USER, DB_PASSWORD, $argv[1], DB_HOST);
	$single_site_url = $single_db->get_var("select option_value from wp_options where option_name = 'home' ") or die('Failed to connect.');
	$single_site_name = $single_db->get_var(" select option_value from wp_options where option_name = 'blogname' ;");
	
	$root_site_domain = $wpdb->get_var(sprintf("select domain from %s limit 1 ;", $wpdb->prefix . 'site'));

	message("* Found WP database from $single_site_url ($single_site_name)");
	$new_path = ask("** Chose a path for the blog (please include backslashes:");

	$new_site_url = 'http://' . $root_site_domain . $new_path  ;

	$new_blog_id = wpmu_create_blog($root_site_domain, $new_path, $single_site_name, 1, null, 1);
	
	$new_table_names = array() ; $old_tables = array() ; $new_wp_options_table = "" ; $new_posts_table = "" ;

	foreach ($single_db->get_col("show tables",0) as $table_name) {
		$old_tables[]= $table_name ;
		$arr = explode('_', $table_name); 
		array_splice($arr, 1, 0, $new_blog_id);
		$arr[0] = str_replace("_", "", $wpdb->prefix) ; 
		$new_table_name = implode('_', $arr);
		
		if($table_name == 'wp_options') $new_wp_options_table = $new_table_name ;
		if($table_name == 'wp_posts') $new_posts_table = $new_table_name ;

		if($table_name != 'wp_users' and $table_name != 'wp_usermeta'){
			$new_table_names[]= $new_table_name ;
			$single_db->query("create table $new_table_name like $table_name ; ");
			$single_db->query("insert into $new_table_name select * from $table_name ;");
		}
	}

	foreach ($single_db->get_results("select * from wp_users", ARRAY_A) as $user) {
		$old_user_id = $user['ID'] ;
		foreach (array('spam', 'deleted', 'user_status', 'user_activation_key', 'ID') as $field) {
			unset($user[$field]);
		}
		if( ! $user_id = $wpdb->get_var(sprintf("select id from $wpdb->users where user_email = '%s'", $user['user_email']) )  ){
			$user_id = wp_insert_user($user) ;
			wp_insert_user(array('ID'=> $user_id, 'user_pass' => $user['user_pass'])) ;	
		}
		

		$metadatas = $single_db->get_results( 
			"select meta_key, meta_value from wp_usermeta where user_id = $old_user_id " ) ;
		foreach ($metadatas as $metadata) {
			if($metadata->meta_key == 'wp_capabilities') continue ;
			add_user_meta($user_id, $metadata->meta_key, maybe_unserialize($metadata->meta_value), true ) ;
		}

		$single_db->query(sprintf( 
				"update $new_posts_table set post_author = %s where author_id = %s", 
				$user_id, $old_user_id  )) ;

	}


	$cmd = sprintf( "mysqldump -u%s -p%s -h %s %s %s  %s > dump.sql", 
		DB_USER, DB_PASSWORD, DB_HOST, $single_db_name, implode(" ", $new_table_names),
		$lock_tables ? "" : "--lock-tables=false"
	);
	system($cmd) ;

	$cmd = sprintf("mysql -u%s -p%s -h %s %s < dump.sql", 
		DB_USER, DB_PASSWORD, DB_HOST, DB_NAME );
	system($cmd);

	foreach ($new_table_names as $table_name){
		$single_db->query("drop table $table_name ;") ;
	}

	$query = "update $new_wp_options_table set option_value =
		replace(option_value, '$single_site_url', '$new_site_url' ); ";
	$wpdb->query($query);
	echo ("replaced $single_site_url with $new_site_url") ;
	$new_domain = ask("** Do you want to map a domain to it? (leave blank if no)") ;
	if ($new_domain) {
		$wpdb->query(sprintf("insert into %s values('', $new_blog_id, '$new_domain', 1) ;", $wpdb->prefix . 'domain_mapping' ));
	}

	$destroy_old_tables = ask("** Do you want to destroy the old $single_site_name tables? (blank for no)") ;
	if ($destroy_old_tables) {
		foreach ($old_tables as $table) {
			$single_db->query("drop table $table") ;
		}
	}

 ?>
