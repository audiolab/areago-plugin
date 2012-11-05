<?php
if (!class_exists('SoundWalk')){
	class SoundWalk {

		var $soinu_dir="";
		var $soinu_URL="";
		var $count=0;
		

	}	
}

$SoinuDroid=new SoundWalk();

function loadSoundWalks(){
	
	global $wpdb;
	
	$tabla=WALKS_TABLE;
	$q="SELECT * FROM $tabla WHERE 1=1";
	
	 return $wpdb->get_results($q,'ARRAY_A');
}

function loadSoundWalk($id){
	global $wpdb;
	
	$tabla=WALKS_TABLE;
	$q="SELECT * FROM $tabla WHERE id=$id";
	return $wpdb->get_results($q,'ARRAY_A');
}

/**
 * Check if any SoundWalk Exists on the system
 *
 *
 * @since 3.0
 *
 * @return boolean True if exist, otherwise, false.
 *
 */

function checkifSoundWalkExists(){
	
	global $SoinuDroid;
	
	checkSoundWalkDirectories();	
	$sw_count=getSoundWalkCount();	
	$SoinuDroid->count=$sw_count;		
	if ($sw_count==0){
		return false;
	}else{
		return true;
	}
		
}

function initializeSoundWalks(){
	$walks_exists=checkifSoundWalkExists();
	
	if ($walks_exists){
		loadSoundWalksInfo();
	}

}

function loadSoundWalksInfo(){
	global $wpdb;
	$tabla=$wpdb->prefix . "soinudroid";
	$q="SELECT * FROM $tabla WHERE 1=1";
	$results=$wpdb->get_results($q,ARRAY_A);
}

function getSoundWalkCount(){
	
	global $wpdb;
	$tabla=$wpdb->prefix . "soinudroid";
	$soundwalks_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tabla"));
	return $soundwalks_count;
}

/**
 * Check if the soinudroid directory exist and if it is writable
 *
 *
 * @since 3.0
 *
 * @return boolean True if exist and is writable, otherwise, false.
 *
 */
function checkSoundWalkDirectories(){
	
	$uploads = wp_upload_dir();
	$uploads_basedir=$uploads["basedir"];
	$uploads_baseURL=$uploads["baseurl"];

	$sound_walk_dir=$uploads_basedir . "/soinudroid";
	$dir_exists=is_dir($sound_walk_dir);	
	if (!$dir_exists){
		wp_die( __("The folder $sound_walk_dir doesn't exist.") );
	}
	$is_writable=is_writable($sound_walk_dir);
	if (!$is_writable){
		wp_die( __("The folder $sound_walk_dir is not writable.") );
	}
	global $SoinuDroid;
	$SoinuDroid->soinu_dir=$sound_walk_dir;
	$SoinuDroid->soinu_URL=$uploads_baseURL . "/soinudroid";
	return true;
}

function URLSoundWalkFolder($name){
	
	$path_exists=checkSoundWalkDirectories();
	
	if (!$path_exists){		
		return "error";
	}
	
	global $SoinuDroid;
	$base_URL=$SoinuDroid->soinu_URL;
	$dir_name=sanitizeWalkName($name);
	$dir_name=$base_URL . "/" . $dir_name;		
	return $dir_name;	
}

function createSoundWalkDirectory($name){
	
	$path_exists=checkSoundWalkDirectories();
	
	if (!$path_exists){		
		return "error";
	}
	
	global $SoinuDroid;
	$base_path=$SoinuDroid->soinu_dir;
	$dir_name=sanitizeWalkName($name);
	$dir_name=$base_path . "/" . $dir_name;
	$dir_exists=is_dir($dir_name);
	$succes=true;
	if(!$dir_exists){		
		$succes=mkdir($dir_name);		
	}
	if ($succes==false){		
		return "error";		
	}	
	return $dir_name;
}


function sanitizeWalkName($name){
	
	$new_name=sanitize_file_name($name);
	return $new_name;
}

function createSoundWalkFile($walk_dir, $info){	
	$fp = fopen($walk_dir . "/soundwalk.json", 'w');
	fwrite($fp, $info);
	fclose($fp);	
}

function compressSoundFilesToFolder($walk_dir, $files){
	require_once '../compress.php';
	
	if (is_null($files)){
		return false;
	}
	$zip_file = new zip_file($walk_dir . '/sound_files.zip');
	$zip_file->set_options(array('basedir'=>".",'overwrite'=>1,'level'=>0, 'storepaths'=>0));

	$zip_file->add_files($files);
	$zip_file->create_archive();

	if (count($zip_file->errors) > 0){
		//return FALSE;
	}
	return true;

}

function generateHASHInfoFile($walk_dir){
	if ($walk_dir==""){
		exit();
	}
	$info_hash=hash_file('md5',$walk_dir . "/soundwalk.json");
	$zip_hash=hash_file('md5',$walk_dir . "/sound_files.zip");
	$hashes= array();
	array_push($hashes, $info_hash);
	array_push($hashes, $zip_hash);
	$data=json_encode($hashes);
	$fp = fopen($walk_dir . "/info.json", 'w');
	fwrite($fp, $data);
	fclose($fp);			
}
?>
