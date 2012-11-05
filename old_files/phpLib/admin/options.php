<div class="wrap">
	<script>
		function onMapMove () {
			var pos=map.getCenter();
			jQuery("#originLat").val(pos.lat());
			jQuery("#originLong").val(pos.lng());
		};
		function onMapZoom () {
			var zoom=map.getZoom();
			jQuery("#zoom").val(zoom);
		};

		function onMapType () {
			var mapType=map.getMapTypeId();
			jQuery("#mapType").val(mapType.toUpperCase());
		}
	</script>
	<?php
	global $sm_config;
	$originLat = $sm_config['options']["originLat"];
	$originLong = $sm_config['options']["originLong"];
	$zoom = $sm_config['options']["originZoom"];
	$mapType = $sm_config['options']["mapType"];
	$radius = $sm_config['options']['originRadius'];

	$config = Array(
	    'positionable_marker' => false,
	    'container' => 'map',
	    'map_dragend' => 'onMapMove',
	    'map_zoom' => 'onMapZoom',
	    'mapType_Changed' => 'onMapType',
	    'originLat' => $originLat,
	    'originLong' => $originLong,
	    'zoom' => $zoom,
	    'type' => $mapType
	);
	insertMapScript($config);
	?>
	<h2>SoinuMapa</h2>
	<form method="post" action="">
		<?php wp_nonce_field('update-options'); ?>
		<div id="first">
			<div class="titleHeading"><span>Original position and zoom</span></div>
			<div class="mapContent">
				<div id="map" ></div>
				<div id="mapcontrols">
					<p><label><?php echo 'Latitude: ' ?></label><input type="text" name="originLat" id="originLat" value="<?php echo $originLat ?>"></p>
					<p><label><?php echo 'Longitude: ' ?></label><input type="text" name="originLong" id="originLong" value="<?php echo $originLong ?>"></p>
					<p><label><?php echo 'Zoom: ' ?></label><input type="text" name="zoom" id="zoom" value="<?php echo $zoom ?>"></p>
					<p><label><?php echo 'Radius: ' ?></label><input type="text" name="radius" id="radius" value="<?php echo $radius ?>"></p>
				</div>
			</div>
		</div>
		<div id="second">
			
		</div>
		<input type="hidden" name="mapType" id="mapType" value="<?php print $mapType; ?>" />
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="soinumapa" />
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
