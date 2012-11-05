<?php

$directory_path = dirname(__FILE__);

require( '../../../wp-load.php' );
include_once "$directory_path/phpLib/compress.php";

global $wpdb;
$tabla=MARKERS_TABLE;
$posts_tabla=POSTS_TABLE;
$q = "SELECT $tabla.id, lat, lng, radius FROM $posts_tabla INNER JOIN $tabla ON $posts_tabla.ID=$tabla.id WHERE post_status='publish' AND post_type='post'";
return  $wpdb->get_results($q, 'ARRAY_A');

?>