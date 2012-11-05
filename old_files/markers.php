<?php
header('Content-type: application/json');
require( '../../../wp-load.php' );
require(dirname(__FILE__) . "/FastJSON.class.php");

$nodes = array();
global $wpdb;
$prefix = $wpdb->prefix;

$json = new FastJSON();
$markers=array ();

$qs=$_SERVER['QUERY_STRING'];

if (isset($_GET["numberposts"]) && ($qs=="numberposts=-1")){
	$markers=jloadAllMarkers();
}elseif (isset($_GET['cat'])){
	$markers=loadCategoria($_GET['cat']);
}elseif (isset($_GET['tag'])){
	$markers=loadCategoria($_GET['tag']);
}else{
	die();
}

if (!is_null($markers)):
	foreach ($markers as $key=>$value) {
			
			$obj = new stdClass();
			$obj->post_id = $value['id'];
			$obj->lat = $value['lat'];
			$obj->lng = $value['lng'];
			$obj->radio = $value['radius'];
			array_push($nodes, $obj);
	}
endif;

echo $json->encode($nodes);

function loadCategoria($id){
	global $wpdb;
	$tabla_markers=MARKERS_TABLE;
	$tabla_meta=POST_META_TABLE;
	$tabla_posts=POSTS_TABLE;
	$tabla_terms=$wpdb->terms;
	$tabla_taxonomies=$wpdb->term_taxonomy;
	$tabla_relations=$wpdb->term_relationships;
	$q="SELECT $tabla_posts.ID FROM $tabla_posts INNER JOIN (( $tabla_terms INNER JOIN $tabla_taxonomies ON $tabla_terms.term_id=$tabla_taxonomies.term_id) INNER JOIN $tabla_relations ON $tabla_relations.term_taxonomy_id=$tabla_taxonomies.term_taxonomy_id) ON $tabla_posts.ID=$tabla_relations.object_id WHERE $tabla_terms.name='$id'AND $tabla_posts.post_status='publish'";
	$t=$wpdb->get_results($q,'ARRAY_A');

	if (!is_null($t)){
		return loadMarkers($t);
	}
}


function loadMarkers ($id) {

	global $wpdb;
	$tabla_markers=MARKERS_TABLE;

	if (is_array($id)){
		if (count($id)){
			$t="";
			foreach($id as $node){
				$ids[]=$node['ID'];
			}			
				
			$t=implode(" OR $tabla_markers.id=", $ids);
			$q="SELECT $tabla_markers.id, $tabla_markers.lat, $tabla_markers.lng FROM $tabla_markers WHERE $tabla_markers.id=$t";	
		}
	}else{
		$q="SELECT $tabla_markers.id, $tabla_markers.lat, $tabla_markers.lng FROM $tabla_markers WHERE $tabla_markers.id=$ids";	
	}
	
	return $wpdb->get_results($q,'ARRAY_A');
}

function jloadAllMarkers() {

	global $wpdb;
	$tabla=MARKERS_TABLE;
	$posts_tabla=POSTS_TABLE;
	$q = "SELECT $tabla.id, lat, lng, radius FROM $posts_tabla INNER JOIN $tabla ON $posts_tabla.ID=$tabla.id WHERE post_status='publish' AND post_type='post'";
	return  $wpdb->get_results($q, 'ARRAY_A');
}

function memoria(){
	echo 'Memoria usada: ' . round(memory_get_usage() / 1024,1) . ' KB'; ?>
<?php echo get_num_queries(); ?> queries in <?php timer_stop(1); ?> seconds.<?

}

?>


