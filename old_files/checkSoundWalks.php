<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
header('Content-type: application/json');
define('DOING_AJAX', true);
define('WP_ADMIN', true);

require( '../../../wp-load.php' );

require_once('../../../wp-admin/includes/admin.php');
require(dirname(__FILE__) . "/FastJSON.class.php");

require_once ('phpLib/soinudroid_utils.php');



$json = new FastJSON();
$walks=loadSoundWalks();

$data= array();

foreach ($walks as $key => $value) {
	$obj=new stdClass();
	$obj->id = $value['id'];
	$obj->name = $value['name'];	
	$obj->description = $value['description'];
	$markers=unserialize($value["data"]);
	$total_markers=(string) count($markers);
	$obj->markers = $total_markers;
	$obj->folder=URLSoundWalkFolder($value['name']);
	array_push($data, $obj);				
}

echo $json->encode($data);

?>
