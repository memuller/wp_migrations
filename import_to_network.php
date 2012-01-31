<?php
	require_once '../wp-migrations-config.php' ;
	require_once 'lib/utils.php' ;
	require_once 'lib/wp-load.php';

	require 'lib/ez_sql/shared/ez_sql_core.php' ;
	require 'lib/ez_sql/mysql/ez_sql_mysql.php' ;

	$single_db_name = $argv[1];

	$single_db = new ezSQL_mysql(DB_USER, DB_PASSWORD, $argv[1], DB_HOST);
	$single_site_url = $single_db->get_var("select option_value from wp_options where option_name = 'home' ") or die('Failed to connect.');
	$single_site_name = $single_db->get_var(" select option_value from wp_options where option_name = 'blogname' ;");
	$root_site_domain = $wpdb->get_var(sprintf("select domain from %s limit 1 ;", $wpdb->prefix . 'site'));
	
	message("* Found WP database from $single_site_url ($single_site_name)");
	$new_path = ask("** Chose a path for the blog (please include backslashes:");

	$new_site_url = 'http://' . $root_site_domain . $new_path  ;
	$query = "update wp_options set option_value = 
		replace(option_value, '$single_site_url', '$new_site_url' ); ";
	$single_db->query($query);
	
	$new_blog_id = wpmu_create_blog($root_site_domain, $new_path, $single_site_name, 1, null, 1);

	foreach ($single_db->get_col("show tables",0) as $table_name) {
		$arr = explode('_', $table_name);
		array_splice($arr, 1, 0, $new_blog_id);
		$arr[0] = $wpdb->prefix ; 
		$new_table_name = implode('_', $arr);
		$single_db->query("create table $new_table_name like $table_name ; ");
		$single_db->query("insert into $new_table_name select * from $table_name ;");
		$single_db->query("drop table $table_name");
	}

	$cmd = sprintf("mysqldump -u %s -p%s -h %s %s > dump.sql", DB_USER, DB_PASSWORD, DB_HOST, $single_db_name);
	system($cmd) ;
	$cmd = sprintf("mysql -u %s -p%s -h %s %s < dump.sql", DB_USER, DB_PASSWORD, DB_HOST, DB_NAME);
	system($cmd);

	$new_domain = ask("** Do you want to map a domain to it? (leave blank if no)") ;
	if ($new_domain) {
		$wpdb->query(sprintf("insert into %s values('', $new_blog_id, '$new_domain', 1) ;", $wpdb->prefix . 'domain_mapping' ));
	}

 ?>