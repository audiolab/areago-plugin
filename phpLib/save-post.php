<?php

require( '../../../../wp-load.php' );

global $SoinuMapa;
global $wpdb;

$filename=$_POST["fileName"];
$attachment=Array(
	"post_title"=>$filename,
    "post_content"=>"",
    "post_status"=>'inherit',
    "post_mime_type"=>'audio/mpeg',
    "guid"=>$_POST['fileURL']
);

$attach_id = wp_insert_attachment( $attachment, $filename);
//$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
//wp_update_attachment_metadata( $attach_id,  $attach_data );
            
$post_d = array(
	'post_category' => array($_POST['categoria']),
	'post_content' => $_POST['contenido'],
	'post_status' => 'publish', 
	'post_title' => $_POST['titulo'],
	'post_type' => 'post',
				  //'tags_input' => [ '<tag>, <tag>, <...>' ] //For tags.
);
			
$id=wp_insert_post($post_d);
$mark=Array(
	'type'=>'audio',
    'attachmentID'=>$attach_id,
    'file'=>$_POST['fileURL']
);    
$record=maybe_serialize($mark);
$lat=$_POST['posLat'];
$long=$_POST['posLong'];
    
$q = 'INSERT INTO ' . $wpdb->prefix . 'sm_SoundPost (id, lat, lng, recording) VALUES (' . $id . ',' . $lat . ',' . $long . ",'" . $record . "');";
$SoinuMapa->markers[$id]['lat']=$lat;
$SoinuMapa->markers[$id]['lng']=$long;
$SoinuMapa->markers[$id]['recording']=$mark;
    
$resultado=$wpdb->query($q);

if($resultado===false){

	echo "error";
}else{
	echo "correcto";
}

?>