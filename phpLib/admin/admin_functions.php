<?php


//_______CONFIGURATION___________//
// Add the configuration option to the administration panel
function addAdminPages() {
	//$pageName = add_options_page("SoinuMapa Options", "SoinuMapa", 'manage_options', basename(__FILE__), "adminOptions");
	$pageName = add_menu_page("SoinuMapa", "SoinuMapa", 'manage_options', 'soinumapa_options', 'adminOptions');
	add_submenu_page('soinumapa_options', "Soinumapa SoundWalk", 'SoinuDroid', 'manage_options', 'soinumapa_options_droid','adminDroid');
}

//Callback for the options setup.
function adminOptions() {
	
	 if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	global $SoinuMapa;
	if (isset($_POST["page_options"])) {
		//Save data!!
		save_options();
	}
	echo '<link rel="stylesheet" href="' . SOINUMAPA_URL . '/phpLib/admin/css/options.css" type="text/css" />';
	include_once ( SOINUMAPA_LIB . 'admin/options.php' );
	unset($SoinuMapa);
}

function adminDroid(){
	if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if (isset($_POST["soinudroid"])) {
		//Save data!!
		krumo($_POST);
		krumo($_GET);
		 krumo($_SERVER, $_ENV);
	}
	
	echo '<script> var ajax_url="' . SOINUMAPA_URL .'/phpLib/admin/ajax-functions.php"; </script>';
	echo '<script> var path_url="' . SOINUMAPA_URL .'"; </script>';
	
	echo '<script src="' . SOINUMAPA_URL . '/phpLib/admin/js/jquery.form.js" type="text/javascript"></script>';		
	echo '<script src="' . SOINUMAPA_URL . '/js/jplayer/jquery.jplayer.min.js" type="text/javascript"></script>';		
	echo '<script src="' . SOINUMAPA_URL . '/phpLib/admin/jGrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>';
	echo '<script src="' . SOINUMAPA_URL . '/phpLib/admin/js/soinudroid.js" type="text/javascript"></script>';
	echo '<script src="' . SOINUMAPA_URL . '/phpLib/admin/js/mapsCore.js" type="text/javascript"></script>';
	echo '<link rel="stylesheet" href="' . SOINUMAPA_URL . '/phpLib/admin/jGrid/css/ui.jqgrid.css" type="text/css" />';
	echo '<link rel="stylesheet" href="' . SOINUMAPA_URL . '/phpLib/admin/jGrid/css/soinudroid/jquery-ui-1.8.13.custom.css" type="text/css" />';
	echo '<link rel="stylesheet" href="' . SOINUMAPA_URL . '/phpLib/admin/css/soinudroid.css" type="text/css" />';
	echo '<link rel="stylesheet" href="' . SOINUMAPA_URL . '/js/jplayer/player.css" type="text/css" />';
	include_once (SOINUMAPA_LIB . 'soinudroid_utils.php');
	include_once ( SOINUMAPA_LIB . 'admin/soinu_droid.php' );
	
	
	
}


//____________POST INSERT MEDIA FUNCTIONS_____________//
//Inserts the marker button into the New Post page.
//If the marker exists, the icon will be green, else, will be gray.
function sm_addMarkerButton($admin = true) {

	global $post_ID, $temp_ID, $SoinuMapa;

	$ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);

	$exists = markerExists($ID);

	$icono = "soinu-icon.gif";
	if ($exists) {
		$icono = 'soinu-icon-green.gif';
	}

	$media_mapa_src = get_option('siteurl') . '/wp-admin/media-upload.php?post_id=' . $ID;
	$media_mapa_iframe_src = apply_filters('media_mapa_iframe_src', "$media_mapa_src&amp;type=soinumapa&amp;tab=soinumapa");

	echo "<a class=\"thickbox\" href=\"{$media_mapa_iframe_src}&amp;TB_iframe=true\" title=\"Add Map Point\"><img src=\"" . SOINUMAPA_URL . "/" . $icono . "\" alt=\"Add Map Point\" /></a>";
}
//___________________POST MEDIA FUNCTIONS_________________//
//Inserts / saves the data from the form of Soinumapa on a posts edit window.
function sm_media_upload($param) {

	global $SoinuMapa;

	$errors = array();

	$id = $_GET['post_id'];

	$SoinuMapa->actual_post_ID = $id;

	$exists = markerExists($id);

	if (isset($_POST['soinumapaAdd'])) {
		//Debemos guardar los datos
		saveDataPost();
		printCloseWindowScript();
	}
	//trigger_error($param, E_USER_NOTICE);
	return wp_iframe('sm_media_upload_form', 'soinumapa', $errors, $id, "soinumapa", $exists);
}

function sm_media_upload_form($type, $errors = null, $id = null, $tab = null, $exists=false) {

	include_once (SOINUMAPA_LIB . "/admin/media.php");
}


function audio_fields_erase_buttons( $form_fields, $post ){
	$form_fields['buttons'] = array(
		'label' => __('Seleccionar'),
		'input' => 'html',
		'html'  => "<input type='button' class='button' name='addRec' value='" . esc_attr__('Insert into Post') . "' onclick='insertToPost($post->ID)' />",
	);

	return $form_fields;
}

/**
 * Prints the close windows script. For media upload
 *
 * Prints on the browser the javascript function who close the Tick Box window for media upload
 *
 * @since 2.0
 *
 *
 */
function printCloseWindowScript() {
?>
	<script type="text/javascript">
		/* <![CDATA[ */
		var win = window.dialogArguments || opener || parent || top;
		win.tb_remove();
		/* ]]> */
	</script>
<?php
}

/**
 * Hook for delete_post
 *
 * Check if the marker exists in the db and delete it.
 *
 *
 * @since 2.0
 *
 * @param int $id The id of the selected post.
 *
 */
function deleteMarker($id) {

	global $wpdb;

	if (markerExists($id)) {

		$tabla = MARKERS_TABLE;
		$q = "DELETE FROM $tabla WHERE id = $id";
		$wpdb->query($q);
	}
}

function saveDataPost() {

	global $wpdb;

	$action_m = $_POST["soinumapaAdd"];

	$attID = $_POST['mediaID'];
	$post = get_post($attID);
	
	$mark = Array(
    	'type' => 'audio',
	    'attachmentID' => $_POST["mediaID"],
    	'file' => $post->guid
	);

	$record = mysql_real_escape_string(maybe_serialize($mark));

	$lat = $_POST['posLat'];
	$long = $_POST['posLong'];
	$id = $_POST['post_id'];
	$radius=$_POST['radius'];
	$tabla = MARKERS_TABLE;

	if ($action_m == "add") {
		$q = "INSERT INTO $tabla (id, lat, lng, radius, data) VALUES (\"$id\", \"$lat\", \"$long \", \"$radius\", \"$record\" );";
	} elseif ($action_m == "update") {
		$q = "UPDATE $tabla SET lat=\"$lat\", lng=\"$long\", data=\"$record\" , radius=\"$radius\" WHERE id=\"$id\";";
	}
	$wpdb->query($q);
}
?>
