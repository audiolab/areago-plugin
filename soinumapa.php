<?php

//include("class.krumo.php");

/**
 * @package SoinuMapa
 * @author AudioLab -- Xavi Balderas
 * @version 3.0 Beta
 */
/*
  Plugin Name: SoinuMapa
  Plugin URI: http://wordpress.org/#
  Description: SoinuMapa Plugin with audiogides hack.
  Author: AudioLab
  Version: 3.0
  Author URI: http://www.soinumapa.net
 */

load_plugin_textdomain('soinumapa');

define('SOINUMAPA_DIR', WP_CONTENT_DIR . "/plugins/" . plugin_basename(dirname(__FILE__)));
define('SOINUMAPA_URL', WP_CONTENT_URL . "/plugins/" . plugin_basename(dirname(__FILE__)));
define('SOINUMAPA_LIB', SOINUMAPA_DIR . '/phpLib/');

global $wpdb;

define('MARKERS_TABLE', $wpdb->prefix . "sm_markers");
define('ICONS_TABLE', $wpdb->prefix . "sm_icons");
define ('POSTS_TABLE', $wpdb->prefix . 'posts');
define ('POST_META_TABLE', $wpdb->prefix . 'postmeta');
define ('WALKS_TABLE', $wpdb->prefix . 'soinudroid');

require_once(SOINUMAPA_LIB . "config.php");

require_once(SOINUMAPA_LIB . "core-functions.php");
require_once(SOINUMAPA_LIB . "hooks.php");
require_once( SOINUMAPA_LIB . "googleMapsCore.php");

if (is_admin ()) {
	require_once (SOINUMAPA_LIB . "admin/admin_functions.php");
}

if (!class_exists('SoinuMapa')) {

	class SoinuMapa {

		var $options_page = 'SoinuMapaOptions';
		var $actual_post_ID;

		var $marker;
		
		var $marcadores;
		

		function SoinuMapa() {
			$this->marcadores =$this->loadMarkers();
		}

		function loadMarkers() {
			global $wpdb;
			$tabla=MARKERS_TABLE;
			$posts_tabla=POSTS_TABLE;
			if (is_admin ()) {
				$q = "SELECT $posts_tabla.ID FROM $posts_tabla INNER JOIN $tabla ON $posts_tabla.ID=$tabla.id WHERE post_type='post'";
			} else {
				$q = "SELECT $posts_tabla.ID FROM $posts_tabla INNER JOIN $tabla ON $posts_tabla.ID=$tabla.id WHERE post_status='publish' AND post_type='post'";
			}
			$results = $wpdb->get_col($q);
			$results = array_flip($results);
			return $results;
		}

	}

	/*	 * *********________________________________________________________________________________________________________________********** */
}

register_activation_hook(__FILE__, "registerPlugin");

function registerPlugin() {
	/*
	 *  Create the options
	 */
	global $wpdb;
	global $sm_db_version;

	$table_name = $wpdb->prefix . "sm_icons";
	if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
                                  id mediumint(9) NOT NULL ,
                                  name tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                                  icon LONGTEXT NOT NULL,
				  shadow LONGTEXT NOT NULL,
                                  UNIQUE KEY id (id)
                                )CHARACTER SET utf8 COLLATE utf8_general_ci;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	$table_name = $wpdb->prefix . "sm_markers";
	if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
                                  id mediumint(9) NOT NULL ,
                                  lat DOUBLE NOT NULL,
                                  lng DOUBLE NOT NULL,
                                  radius DOUBLE NOT NULL,
                                  data LONGTEXT NOT NULL,
                                  UNIQUE KEY id (id)
                                )CHARACTER SET utf8 COLLATE utf8_general_ci;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	$table_name = $wpdb->prefix . "soinudroid";
	if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
                                  id mediumint(9) NOT NULL AUTO_INCREMENT ,
		name tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
		description tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                                  data LONGTEXT NOT NULL,
                                  UNIQUE KEY id (id)
                                )CHARACTER SET utf8 COLLATE utf8_general_ci;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
}

$SoinuMapa = new SoinuMapa();


function rss_add_file() {
	global $post;
	global $SoinuMapa;
	$postID = $post->ID;
	the_marker($postID);
	$file = $SoinuMapa->file;
	echo "<enclosure url=\"$file\" type=\"audio/mpeg\" />";
	unset($post, $SoinuMapa);
}

?>
