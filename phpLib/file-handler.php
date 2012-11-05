<?php
	/* Note: This thumbnail creation script requires the GD PHP Extension.  
		If GD is not installed correctly PHP does not render this page correctly
		and SWFUpload will get "stuck" never calling uploadSuccess or uploadError
	 */
require( '../../../../wp-load.php' );
require_once( "FastJSON.class.php");
require_once('getid3/getid3.php');

	// Get the session Id passed from SWFUpload. We have to do this to work-around the Flash Player Cookie Bug
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}

	session_start();
	
		// Check the upload
	if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
		echo "ERROR:invalid upload";
		exit(0);
	}
	
	$file_a=array();
	$json = new FastJSON();
	
	$months_folders=get_option( 'uploads_use_yearmonth_folders' );
	$wud = wp_upload_dir();
	if ($months_folders){
		$destino=$wud['path'];
		$destino_url=$wud['url'];	
	}else{
		$destino=$wud['basedir'];
		$destino_url=$wud['baseurl'];
	}
	
     $prefijo = substr(md5(uniqid(rand())),0,6);
     $nombre_seguro=str_replace(" ", "_", $_FILES["Filedata"]["name"]);
     // guardamos el archivo a la carpeta files
     $destino .= "/" . $prefijo."_".$nombre_seguro;
     if (move_uploaded_file($_FILES['Filedata']['tmp_name'],$destino)) {
		chmod($destino, 0755);
		$file_a["error"]=0;
		$file_a["name"]=$_FILES["Filedata"]["name"];
		$file_a["file"]=$destino;
		$file_a["url"]=$destino_url . "/" . $prefijo."_".$nombre_seguro;
		$getID3 = new getID3;
		$ThisFileInfo = $getID3->analyze($destino);
        getid3_lib::CopyTagsToComments($ThisFileInfo);
        $file_a["duracion"]=$ThisFileInfo['playtime_string'];
        $file_a["bitrate"]=$ThisFileInfo['bitrate'];
		echo $json->encode($file_a);
     }else{
     	$file_a["error"]=1;
     	echo $json->encode($file_a);
     }
     
     
?>