<?php


/**
*
*
* MOD Rewrite Module
*
*/

function generate_rewrite_rules($rules)
{

	global $wp_rewrite;
	$non_wp_rules = array(
		'mapita' => '/wp-content/plugins/SoinuMapa_for_Wordpress/soinudroid.php'
	);

	return $non_wp_rules + $rules;
	
}
/**
 * Check if one marker exists in the db.
 *
 * Check the $markers variable to see if the selected post exists in the db of markers.
 *
 *
 * @since 2.0
 *
 * @param int $id The id of the selected post.
 *
 */
function markerExists($id) {

	global $SoinuMapa;

	if (isset($SoinuMapa->marcadores[$id])) {
		return true;
	} else {
		return false;
	}
	unset($SoinuMapa);
	
}


/**
 * Load the data of one marker into the SoinuMapa class.
 *
 * Load the data stored in the db into the marker variable as an array.
 *
 * @since 2.0
 *
 * @param int $id The id of the marker to retrieve
 */
function the_marker($id) {

	global $wpdb;
	global $SoinuMapa;

	$tabla=MARKERS_TABLE;

	$q = "SELECT  lat, lng, radius, data FROM $tabla WHERE id=$id";

	$result = $wpdb->get_row($q, "ARRAY_A");

	if ( $result ) {
		$SoinuMapa->marker=Array(
			'id'=>$id,
			'lat'=>$result['lat'],
			'long'=>$result['lng'],
			'radius' =>$result['radius'],
			'data'=>  maybe_unserialize( $result[ 'data' ] )			
		);
	}

	unset($wpdb, $q, $result, $SoinuMapa);
}

//Add some javascript to the page.
function addScriptsWP() {
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"), false, '');
		wp_enqueue_script('jquery');
	}
	wp_register_script("soinumapa", SOINUMAPA_URL . "/js/soinumapa.js");
	wp_register_script("jplayer", SOINUMAPA_URL . "/js/jplayer/jquery.jplayer.min.js");	
	wp_register_style( 'jplayer_style', SOINUMAPA_URL . '/js/jplayer/player.css' );
	wp_enqueue_script("soinumapa");
	wp_enqueue_script("jplayer");
	wp_enqueue_style("jplayer_style");
}


//____________________________________________________Theme Tags

function insertMap($config) {

	global $sm_config;

	$originLat = $sm_config['options']["originLat"];
	$originLong = $sm_config['options']["originLong"];
	$zoom = $sm_config['options']["originZoom"];
	$mapType = $sm_config['options']["mapType"];

	$config["zoom"] = $zoom;
	$config["originLat"] = $originLat;
	$config["originLong"] = $originLong;
	$config["type"]=$mapType;

	insertMapScript($config);

	unset($SoinuMapa);
}

function sm_the_markers($posts_query) {
	/**
	 *  Returns the layer for the markers and complete the markers array.
	 */
	//$allPosts = get_posts($posts_query);

	$envio= http_build_query($posts_query);
	global $q_config;
	?>
		<script type="text/javascript">
			var path_url="<?php echo SOINUMAPA_URL ?>";
			var post_lang="<?php echo $q_config['language']?>"
			
			jQuery("document").ready(function(){
				url="<?php echo SOINUMAPA_URL ?>/markers.php?<?php echo $envio ?>";
				jQuery.getJSON(url,function(data){
					if (data.length){
					renewMarkers(data);
					}
				});
			});
		</script>
<?php

	//return new_sm_add_marker($allPosts);
}


function tags($id)
{

	$posttags = get_the_tags($id);
	$t = "";
	if ($posttags) {
		$t.='<p>' . __('Tags', 'soinu') . ': ';
		$max = count($posttags) - 1;
		$j = 0;
		foreach ($posttags as $tag) {
			if ($j < $max) {
				$sep = ", ";
			} else {
				$sep = "";
			}
			$tag_link=get_tag_link($tag->term_id);
			$name=$tag->name . $sep;
			//$t.="<a href='" . $home . "?tag=" . $tag->slug . "&lang=" . $lang_selected . "'>" . $tag->name . $sep . '</a> ';
			$t .= "<a href=\"$tag_link\">$name</a>";

			++$j;
		}
		$t.='</p>';
	}
	return $t;
}

function categories($id) {


	$t = "";
	$t.='<p>' . __('Categories') . ': ';
	$max = count($id) - 1;
	$k = 0;
	foreach ($id as $category) {
		if ($k < $max) {
			$sep = ", ";
		} else {
			$sep = "";
		}
		$category_link = get_category_link( $category->term_id );
		$name=$category->name . $sep;
		//$t .= "<a href='" . $home . '?cat=' . $category->term_id . '&lang=' . $lang_selected . "'>" . $category->name . $sep . '</a>';
		$t .= "<a href=\"$category_link\">$name</a>";
		++$k;
	}
	$t .= '</p>';
	return $t;
}


?>
