<?php

if (is_admin()) {

	add_action('admin_menu' , "addAdminPages" );

	add_action('media_buttons', 'sm_addMarkerButton', 20);

	add_action('delete_post', 'deleteMarker');

	add_action('media_upload_soinumapa', 'sm_media_upload');



} else  {

	add_action('init', 'addScriptsWP');

	add_action("rss2_item", 'rss_add_file');


	add_action('mod_rewrite_rules', 'generate_rewrite_rules');


}


?>
