drop procedure if exists wp_change_hostname;
DELIMITER ;;
CREATE PROCEDURE wp_change_hostname (in new_hostname varchar(255), in new_path varchar(255), in main_wp_tables_have_id_prefix_too boolean )
begin
	# parameters and otherwise configurable stuff
	declare old_hostname varchar(255) default '';
	declare old_path varchar(255) default '' ;
	
	# loop control variables.
	declare ended boolean default false;
	declare found_blogs_count int ;
	declare current_blog_id bigint;
	declare checked_blogs_count int default 0;
	declare old_full_blog_url varchar(255) default '';
	declare new_full_blog_url varchar(255) default '';
	declare full_blog_url varchar(255) default '';
	declare wp_options_table varchar(255) ;
	declare wp_posts_table varchar(255);
	
	declare debug_str varchar(255) default '';
	declare blogs cursor for
		select blog_id from wp_blogs;
			
	# loop breaking handlers.
	declare CONTINUE HANDLER for NOT FOUND
		set ended = true ;
		
	# gets old path and domain.
	if old_hostname = '' then
		select domain from wp_site limit 1 into old_hostname;
	end if;
	select path from wp_site where domain = old_hostname limit 1 into old_path;
	
	# defaults path, if none where specified.
	if new_path = '' then
		set new_path = old_path ;
	end if;
	
	open blogs ;
	select found_rows() into found_blogs_count ;
	wp_options_loop: LOOP
		
		fetch blogs into current_blog_id ;
		if found_blogs_count = checked_blogs_count then
			close blogs ; leave wp_options_loop ;
		end if;
    	if current_blog_id = 1 and main_wp_tables_have_id_prefix_too = false then 
        	set current_blog_id = null ;
    	end if;
    	
		set wp_options_table = concat_ws('_', 'wp', current_blog_id, 'options');

		set @q := concat('update ', 
							wp_options_table, 
							" set option_value = replace(option_value, '", 
							concat(old_hostname, old_path), 
							"', '", 
							concat(new_hostname, new_path), 
						"');");
		prepare statement from @q ;
		execute statement ;
		set checked_blogs_count = checked_blogs_count +1 ;
	
	end loop wp_options_loop ;
	
	update wp_site set domain = new_hostname, path = new_path where domain = old_hostname ;
	if old_path = '/' then
		update wp_blogs set domain = new_hostname, path = concat(new_path, path) where domain = old_hostname;
		update wp_blogs set path = replace(path, '//', '/') where domain = new_hostname;
	else
		update wp_blogs set domain = new_hostname, path = replace(path, old_path, new_path) where domain = old_hostname ;
	end if;
	
	select found_blogs_count, checked_blogs_count, debug_str ;
end;;
DELIMITER ;