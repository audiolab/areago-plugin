<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if (!class_exists('Areago_List_Table')){

	class Areago_List_Table extends WP_List_Table{

	}//class Areago_List_Table

}