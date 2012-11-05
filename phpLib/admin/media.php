<?php
//function media_upload_form($type, $errors = null, $id = null, $tab = null, $exists=false)
//Outputs the form for media select in the post edit screen and the positions of the marker.

global $SoinuMapa;
global $sm_config;
global $wpdb;


// the original values
$posLat = $sm_config['options']["originLat"];
$posLong = $sm_config['options']["originLong"];
$posRadius=$sm_config['options']["originRadius"];
$zoom = $sm_config['options']["originZoom"];
$mapType = $sm_config['options']["mapType"];

$action_m = "add";

$mediaID = 0;

if ($exists) {
	// We have to update the marker
	the_marker($id);
	$posLat = $SoinuMapa->marker['lat'];
	$posLong = $SoinuMapa->marker['long'];
	$posRadius = $SoinuMapa->marker['radius'];
	$mediaID = $SoinuMapa->marker['data']['attachmentID']; 
	$action_m = "update";
}

$form_action_url = admin_url("media-upload.php?type=$type&tab=$tab&post_id=$id");

//Configuration of the map
$config = Array(
    'positionable_marker' => true,
    'container' => 'map',
    'map_click' => 'onMapClick',
    'originLat' => $posLat,
    'originLong' => $posLong,
    'originRadius' => $posRadius,
    'zoom' => $zoom,
    'type' => $mapType
);

// Loading the stylesheets & javascripts for the form
$style_file = SOINUMAPA_URL . "/phpLib/admin/css/media-uploader.css";
$script_file = SOINUMAPA_URL . "/phpLib/admin/js/media-uploader.js";
$map_functions = SOINUMAPA_URL . "/phpLib/admin/js/mapsCore.js";
$style_media = get_option("siteurl") . "/wp-admin/css/media.css";

echo "<link rel=\"stylesheet\" href=\"$style_file\" type=\"text/css\" media=\"screen\" />\n";
echo "<link rel=\"stylesheet\" href=\"$style_media\" type=\"text/css\" media=\"screen\" />\n";
echo "<script type=\"text/javascript\" src=\"$script_file\"></script>\n";
echo "<script type=\"text/javascript\" src=\"$map_functions\"></script>\n";
//______________________
// Now, we add some javascript needed for the functionality.
?>

<script type="text/javascript">
	
	post_id = <?php echo $id; ?>
	
</script>
<?php
// Adding the map to the form
insertMapScript($config);
?>
<div class="normalForm">
	<?php       
	// Here we have to add the list of audio files listed on the media library

//__________________________***********************	

	global $wp_query, $wp_locale, $post_mime_types;
	
	$tab="audio";

	$post_id = intval($_REQUEST['post_id']);

//	$form_action_url = admin_url("media-upload.php?type=$type&tab=library&post_id=$post_id");
//	$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);
	
	$_GET['paged'] = isset( $_GET['paged'] ) ? intval($_GET['paged']) : 0;
	if ( $_GET['paged'] < 1 )
		$_GET['paged'] = 1;
	$start = ( $_GET['paged'] - 1 ) * 10;
	if ( $start < 1 )
		$start = 0;
	add_filter( 'post_limits', create_function( '$a', "return 'LIMIT $start, 10';" ) );
	list($post_mime_types, $avail_post_mime_types) = wp_edit_attachments_query();
	

?>

<form id="filter" action="" method="get">
<input type="hidden" name="type" value="<?php echo esc_attr( "soinumapa" ); ?>" />
<input type="hidden" name="tab" value="<?php echo esc_attr( "soinumapa" ); ?>" />
<input type="hidden" name="post_id" value="<?php echo (int) $post_id; ?>" />
<input type="hidden" name="post_mime_type" value="<?php echo isset( $_GET['post_mime_type'] ) ? esc_attr( $_GET['post_mime_type'] ) : ''; ?>" />

<p id="media-search" class="search-box">
	<label class="screen-reader-text" for="media-search-input"><?php _e('Search Media');?>:</label>
	<input type="text" id="media-search-input" name="s" value="<?php the_search_query(); ?>" />
	<input type="submit" value="<?php esc_attr_e( 'Search Media' ); ?>" class="button" />
</p>

<div class="tablenav">

<?php
$page_links = paginate_links( array(
	'base' => add_query_arg( 'paged', '%#%' ),
	'format' => '',
	'prev_text' => __('&laquo;'),
	'next_text' => __('&raquo;'),
	'total' => ceil($wp_query->found_posts / 10),
	'current' => $_GET['paged']
));

if ( $page_links )
	echo "<div class='tablenav-pages'>$page_links</div>";
?>

<div class="alignleft actions">
<?php

$arc_query = "SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts WHERE post_type = 'attachment' ORDER BY post_date DESC";

$arc_result = $wpdb->get_results( $arc_query );

$month_count = count($arc_result);

if ( $month_count && !( 1 == $month_count && 0 == $arc_result[0]->mmonth ) ) { ?>
<select name='m'>
<option<?php selected( @$_GET['m'], 0 ); ?> value='0'><?php _e('Show all dates'); ?></option>
<?php
foreach ($arc_result as $arc_row) {
	if ( $arc_row->yyear == 0 )
		continue;
	$arc_row->mmonth = zeroise( $arc_row->mmonth, 2 );

	if ( isset($_GET['m']) && ( $arc_row->yyear . $arc_row->mmonth == $_GET['m'] ) )
		$default = ' selected="selected"';
	else
		$default = '';

	echo "<option$default value='" . esc_attr( $arc_row->yyear . $arc_row->mmonth ) . "'>";
	echo esc_html( $wp_locale->get_month($arc_row->mmonth) . " $arc_row->yyear" );
	echo "</option>\n";
}
?>
</select>
<?php } ?>

<input type="submit" id="post-query-submit" value="<?php echo esc_attr( __( 'Filter &#187;' ) ); ?>" class="button-secondary" />

</div>

<br class="clear" />
</div>
</form>

<div id="library-form">
<script type="text/javascript">
<!--
jQuery(function($){
	var preloaded = $(".media-item.preloaded");
	if ( preloaded.length > 0 ) {
		preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
		updateMediaForm();
	}
});
-->
</script>

<div id="media-items">
<?php add_filter('attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2); 
add_filter('attachment_fields_to_edit', 'audio_fields_erase_buttons',10,2);
?>
<?php echo get_media_items(null, $errors); ?>
</div>
</div>
<?php


//__________________________***********************	

	 
	 
	 
	 ?>
</div>
<div class="normalForm">
    <span id="leyenda">&nbsp;</span><span class="help" style="float:left;">Selected media.</span>
</div>
<!-- THE MAP -->
<div style="margin-top: 0px;">
	<div id="map" style="width:620px; height:350px;display:block;float:left;margin: 10px 0 0px 10px;"></div>
</div>
<!-- THE MAP -->
<!-- THE POINT -->
<div class="clear"></div>
<form method="post" action="<?php echo esc_attr($form_action_url); ?>" class="media-upload-form type-form validate" id="<?php echo $type; ?>-form">
	<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $id; ?>" />
	<input type="hidden" name="soinumapaAdd" id="soinumapaAdd" value="<?php echo $action_m; ?>"/>
	<div id="mapcontrols">
	<label for="posLat">Latitude:</label>
		<input name="posLat" id="posLat"  type="text" value="<?php echo $posLat ?>"><br/>
		<label for="posLong">Longitude:</label>
		<input name="posLong" id="posLong" type="text" value="<?php echo $posLong ?>"><br/>
		<label for="radius">Radius:</label>
		<input name="radius" id="radius" type="text" value="<?php echo $posRadius ?>"><br/>
		<input name="mediaID" id="mediaID" type="hidden" value="<?php echo $mediaID ?>">
	</div>
<?php wp_nonce_field('media-form'); ?>
	<p>
		<input type="submit" class="button-primary" value="Save Marker">
	</p>

</form>
<!-- THE MAP -->
<?php ?>
