<?php

define('DOING_AJAX', true);
define('WP_ADMIN', true);

require( '../../../../../wp-load.php' );
require_once('../../../../../wp-admin/includes/admin.php');
//ob_start();
/*require_once("PhpQuickProfiler.php");
require_once("inicio.php");
$pqp = new PQPExample();
$pqp->init();
*/
//require_once("fb.php");

send_nosniff_header();

do_action('admin_init');

require(dirname(__FILE__) . "/FastJSON.class.php");

require_once ('../soinudroid_utils.php');

if ( isset($_GET["action"])){
	$accion=$_GET['action'];
	if ($accion=="allmarkers"){
		Marcadores();
	}else if ($accion=="loadpost") {
		cargarPost();
	}else if ($accion=="tableMarkers"){
		tablaMarcadores();
	}else if ($accion=="createSoundWalk"){
		saveSoundWalk();
	}else if ($accion=="list_walks"){
		listWalks();
	}else if ($accion=="loadWalk"){
		loadWalk();
	}else if ($accion=="removeWalk"){
		removeWalk();
	}else if($accion=="updateSoundWalk"){
		updateWalk();
	}
	
}else{
	die();
}

function updateWalk(){
	if(!isset($_GET["id"])){
		echo"ERROR";
		exit();
	}
	
	global $wpdb;
	$id=$_GET["id"];
	$walk_name=$_POST['walk_name'];
	$ids=explode (",", $_POST["selectedMarkers"]);
	$walk_description=$_POST['walk_description'];
	$record = mysql_real_escape_string(maybe_serialize($ids));	
	$tabla = WALKS_TABLE;	
	$q = "UPDATE $tabla SET name = \"$walk_name\", description = \"$walk_description\", data = \"$record\" WHERE id=$id;";		
	$wpdb->query($q);
		
}
function removeWalk(){

	if(!isset($_GET["id"])){
		echo"ERROR";
		exit();
	}
	
	global $wpdb;
	
	
	$tabla=WALKS_TABLE;
	$id=$_GET["id"];
	$q="DELETE FROM $tabla WHERE id=$id";
	$wpdb->query($q);
	echo "<p><strong>Deleted</strong></p>";
	
}

function listWalks(){
	

	$obj=new stdClass();
	$walks=loadSoundWalks();
	$records=count($walks);
	$i=0;
	$obj->records=$records;
	$obj->page=1;
	$obj->total=1;	
	foreach ($walks as $key => $value) {
		$obj->rows[$i]['id']=$value["id"];
		$markers=unserialize($value["data"]);
		$total_markers=(string) count($markers);
		$obj->rows[$i]['cell']=array($value['id'],$value['name'],$value['description'],  $total_markers);
		$i++;
	}
	
	echoJSON($obj);
	
}

function echoJSON($ob){		
	header('Content-type: application/json');
	$json = new FastJSON();
	echo $json->encode($ob);	
}
function loadWalk(){
	if(!isset($_GET["id"])){
		echo"ERROR";
		exit();
	}
	
	global $SoinuDroid;
	global $wpdb;
	
	$resultado=loadSoundWalk($_GET["id"]);
	if ($resultado==NULL){
		echo "ERROR";
		exit();
	}
	$res=new stdClass();
	$res->name=$resultado[0]["name"];
	$res->description=$resultado[0]['description'];
	$res->id=$resultado[0]['id'];
	$marcadores=unserialize($resultado[0]['data']);
	
	$t="";
	$t=implode(" OR id=", $marcadores);
	$tabla=MARKERS_TABLE;
	$q = "SELECT id, lat, lng, radius FROM $tabla WHERE id=$t";
	$markers = $wpdb->get_results($q, 'ARRAY_A');	
	$nodes = array();	
	if (is_null($markers)){
		echo "ERROR";
		exit ();
	}
	$i=0;
	foreach ($markers as $key=>$value) {
			
			$obj = new stdClass();
			$id=$value['id'];
			$obj->post_id = $id;
			$obj->lat = $value['lat'];
			$obj->lng = $value['lng'];
			$obj->radio = $value['radius'];			
			$post=get_post($id);			
			setup_postdata($post);
			$obj->title = get_the_title($id);
			$res->rows[$i]['marker']=$obj;
			$i++;
	}
	
	echoJSON($res);	
	
}
function saveSoundWalk(){
	
	if (!isset ($_POST["selectedMarkers"]) || (!isset($_POST['walk_name']))){
		echo "ERROR";
		exit();
	}
	if (($_POST['selectedMarkers']=="") ||($_POST['walk_name']=="")){
		echo "ERROR";
		exit();
	}
	
	
	global $SoinuDroid;
	global $SoinuMapa;		
	global $wpdb;
	
	$walk_name=$_POST['walk_name'];
	$walk_description=$_POST['walk_description'];
	$walk_directory=createSoundWalkDirectory($walk_name);
	
	$tabla=MARKERS_TABLE;
	$posts_tabla=POSTS_TABLE;
	$ids=explode (",", $_POST["selectedMarkers"]);
	$t="";
	$t=implode(" OR id=", $ids);
	$q = "SELECT id, lat, lng, radius, data FROM $tabla WHERE id=$t";
	$markers = $wpdb->get_results($q, 'ARRAY_A');	
	$nodes = array();
	$archivos_audio=array();
	if (is_null($markers)){
		echo "ERROR";
		exit ();
	}
	foreach ($markers as $key=>$value) {
			
			$obj = new stdClass();
			$obj->post_id = $value['id'];
			$obj->lat = $value['lat'];
			$obj->lng = $value['lng'];
			$obj->radio = $value['radius'];			
			the_marker($value['id']);
			$archivo=$SoinuMapa->marker['data']['file'];
			$att_ID=$SoinuMapa->marker['data']['attachmentID'];
			$directorio=get_attached_file( $att_ID );			
			if ($archivo !=null){
				$obj->file = basename($archivo);
				$att_ID=$SoinuMapa->marker['data']['attachmentID'];
				$directorio=get_attached_file( $att_ID );
				array_push($archivos_audio,$directorio);
				
			}
		
			array_push($nodes, $obj);
	}	
	$json = new FastJSON();
	$json_walk_info=$json->encode($nodes);
	createSoundWalkFile($walk_directory, $json_walk_info);
	$compressed_file=compressSoundFilesToFolder($walk_directory, $archivos_audio);
	if (!$compressed_file){		
		exit ();
	}
	saveWalkToDb();
	generateHASHInfoFile($walk_directory);
	
	echo "<p><strong>Data saved<strong></p>";
	
}

function saveWalkToDb(){
	
	global $wpdb;
	
	$walk_name=$_POST['walk_name'];
	$ids=explode (",", $_POST["selectedMarkers"]);
	$walk_description=$_POST['walk_description'];
	$record = mysql_real_escape_string(maybe_serialize($ids));	
	$tabla = WALKS_TABLE;	
	$q = "INSERT INTO $tabla (name, description, data) VALUES (\"$walk_name\", \"$walk_description\", \"$record\" );";		
	$wpdb->query($q);
	
}

function cargarPost(){
	$id=$_GET["postid"];
	$post = get_post($id);
	setup_postdata($post);
	if (is_null($post)){
		die();
	}
	global $SoinuMapa;
	the_marker($id);
	$title = get_the_title($id);
	$archivo=$SoinuMapa->marker['data']['file'];
	?>

	<div class="hentry">
                    <h2><?php echo $title ?></h2>
                    <div class="entry">
		<p></p>	 
		<div id="jquery_jplayer"></div>
		<div class="jp-single-player">
			<div class="jp-interface">
				<ul class="jp-controls">
					<li><button href="#" id="jplayer_play" class="jp-play" tabindex="1">play</button></li>
					<li><button href="#" id="jplayer_pause" class="jp-pause" tabindex="1">pause</button></li>
					<li><button href="#" id="jplayer_stop" class="jp-stop" tabindex="1">stop</button></li>
					<li class="jplayer_buffer">buffering</li>
				</ul>
				<a href="<?php echo $archivo ?>" id="sound_download" tabindex="1" target="_blank">Download</a>
			</div>
		</div>		
		</div>
		    
	</div>

<?php
}

function Marcadores(){
	
	global $wpdb;
	
	$tabla=MARKERS_TABLE;
	$posts_tabla=POSTS_TABLE;
	$q = "SELECT $tabla.id, lat, lng, radius, $posts_tabla.post_title FROM $posts_tabla INNER JOIN $tabla ON $posts_tabla.ID=$tabla.id WHERE post_type='post'";
	$markers = $wpdb->get_results($q, 'ARRAY_A');	
	
	$nodes = array();
	if (!is_null($markers)):
	foreach ($markers as $key=>$value) {
			
			$obj = new stdClass();
			$obj->post_id = $value['id'];
			$obj->lat = $value['lat'];
			$obj->lng = $value['lng'];
			$obj->radio = $value['radius'];
			$obj->title = $value["post_title"];
			array_push($nodes, $obj);
	}
	endif;	
	
	/*$data=array();
	$data['page'] = 1;
	$data['total'] = 1;
	$data['rows'] = array();
	////$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tabla"));
	foreach ($resultados as $key=>$value) {
			$data['rows'][] = array(
			'id' => $value['id'],
			'cell' => array($value['id'], $value['lat'], $value['lng'], $value['radius'], $value['post_title'])
			    );			   
	}

	//krumo($data);*/
	header('Content-type: application/json');
	$json = new FastJSON();
	echo $json->encode($nodes);
}


function tablaMarcadores(){
	

	global $wpdb;	
	$tabla=MARKERS_TABLE;
	$posts_tabla=POSTS_TABLE;
	$q = "SELECT $tabla.id, lat, lng, radius, $posts_tabla.post_title FROM $posts_tabla INNER JOIN $tabla ON $posts_tabla.ID=$tabla.id WHERE post_status='publish' AND post_type='post'";
	$resultados = $wpdb->get_results($q, 'ARRAY_A');		
	$data=array();
	$data['page'] = 1;
	$data['total'] = 1;
	$data['rows'] = array();
	////$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tabla"));
	foreach ($resultados as $key=>$value) {
			$data['rows'][] = array(
			'id' => $value['id'],
			'cell' => array($value['lat'], $value['lng'], $value['radius'], $value['post_title'])
			    );			   
	}

	//krumo($data);
	header('Content-type: application/json');
	$json = new FastJSON();
	echo $json->encode($data);
}

?>
