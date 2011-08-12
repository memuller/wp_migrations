<?php
	define ('PATH', '/home/memuller/Repos/wordpress_apps');
	define ('DOMAIN', 'apps.local');
	require_once 'lib/utils.php' ;
	require_once 'lib/wp-load.php';

	require 'lib/ez_sql/shared/ez_sql_core.php' ;
	require 'lib/ez_sql/mysql/ez_sql_mysql.php' ;

	$single_db_name = $argv[1];

	$single_db = new ezSQL_mysql(DB_USER, DB_PASSWORD, $argv[1], DB_HOST);
	$single_site_url = $single_db->get_var("select option_value from wp_options where option_name = 'home' ") or die('Failed to connect.');
	$single_site_name = $single_db->get_var(" select option_value from wp_options where option_name = 'blogname' ;");
	
	message("* Found WP database from $single_site_url ($single_site_name)");
	$import_type = ask("** Should we import it as a blog or as a site? [blog/site]");

	if($import_type == 'site'){
		$new_site_domain = ask("** What's the new site address for it? (do NOT use http://)");
		$new_site_url = 'http://' . $new_site_domain  ;
		$query = "update wp_options set option_value = 
			replace(option_value, '$single_site_url', 'http://$new_site_url' ); ";
		$single_db->query($query);

		$wpdb->query("insert into wp_site values('', '$new_site_domain', '/' ); ");
		$new_site_id = $wpdb->get_var("select id from wp_site where domain = '$new_site_domain'");
		
		$new_blog_id = wpmu_create_blog($new_site_domain, '/', $single_site_name, 1, null, $new_site_id);

		foreach ($single_db->get_col("show tables",0) as $table_name) {
			$arr = explode('_', $table_name);
			array_splice($arr, 1, 0, $new_blog_id);
			$new_table_name = implode('_', $arr);
			$single_db->query("create table $new_table_name like $table_name ; ");
			$single_db->query("insert into $new_table_name select * from $table_name ;");
			$single_db->query("drop table $table_name");
		}

		$cmd = sprintf("mysqldump -u %s -p%s -h %s %s > dump.sql", DB_USER, DB_PASSWORD, DB_HOST, $single_db_name);
		system($cmd) ;
		$cmd = sprintf("mysql -u %s -p%s -h %s %s < dump.sql", DB_USER, DB_PASSWORD, DB_HOST, DB_NAME);
		system($cmd);

	}else if($import_type == 'blog'){
		
	}else{
		
	}

 ?>