<?php


if (!class_exists('Areago_DB_Helper')){

	class Areago_DB_Helper{
		
		const TABLE_NAME ="areago";
		
		function install(){
			
			global $wpdb;
				
			$table_name = $wpdb->prefix . self::TABLE_NAME;
			
			
			$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			name tinytext NOT NULL,
			description text NOT NULL,
			recordings int DEFAULT 0 NOT NULL,
			language tinytext NOT NULL,
			size int DEFAULT 0 NOT NULL,
			reference longtext DEFAULT '',
			points longtext DEFAULT '',
			hash tinytext DEFAULT '',
			UNIQUE KEY id (id)
			);";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
		}
		
		function get_array_walks(){
			
			global $wpdb;
			$table_name = $wpdb->prefix . self::TABLE_NAME;
			
			$sql = "SELECT * FROM $table_name";
			$res = $wpdb->get_results($sql,ARRAY_A);
			return $res;
			
		}
		
	}//class Areago_DB_Helper
	
	
}