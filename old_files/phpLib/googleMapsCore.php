<?php

/**
 * Prints the Google Maps Api
 *
 * Prints on the browser the javascript loading for the Google Maps Api
 *
 * @since 2.0
 *
 *
 */
function printGoogleApi() {
	echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?libraries=geometry&sensor=true"></script>';
}

/**
 * Prints the map script
 *
 * Prints on the browser the javascript function who implements the map
 *
 * @since 2.0
 *
 * @param string $container The div container who allocates the map.
 * @param float $originLat The Latitude for the origin of the map.
 * @param float $originLong The Longitude for the origin of the map.
 * @param int $zoom The initial zoom.
 *
 */
function printMapScript($container, $originLat, $originLong, $zoom, $mapType) {
	echo "var map;\n";
	echo "function initializeMap() {\n";
	echo "var latlng = new google.maps.LatLng($originLat, $originLong);\n";
	
	echo "var myOptions = {\n";
	echo "          zoom: $zoom,\n";
	echo "          center: latlng,\n";
	echo "          mapTypeId: google.maps.MapTypeId.$mapType,\n";
	echo "          scrollwheel: false,\n";
	echo "			navigationControl: false,\n";
	echo "			streetViewControl: false,\n";
	echo "			navigationControl: true,\n";
	echo "			navigationControlOptions: {\n";
	echo "			  style: google.maps.NavigationControlStyle.SMALL\n";
	echo "			},\n";
	echo "			mapTypeControlOptions: {\n";
	echo "					style: google.maps.MapTypeControlStyle.DROPDOWN_MENU\n";
	echo "			}\n";
	echo "};\n";
	echo "map = new google.maps.Map(document.getElementById(\"$container\"), myOptions);\n";
	echo "after_loading();\n";
	echo "};\n";

	echo "jQuery(\"document\").ready(function(){\n";
	echo "     initializeMap();\n";
	echo "});\n";
}

/**
 * Prints the Google Maps Events
 *
 * Prints on the browser the javascript for the map events catch
 *
 * @since 2.0
 *
  @param mixed $config Array of options to configure the way it shows the map and a
 *                                      callback to execute after the loading of the map, an the loading of the scripts.
 *
 */
function printMapEvents($config) {

	if ($config["map_click"] != "") {
		$callback = $config['map_click'];
		echo "google.maps.event.addListener(map, 'click', $callback);\n";
	}

	if ($config["mapType_Changed"] != "") {
		$callback = $config['mapType_Changed'];
		echo "google.maps.event.addListener(map, 'maptypeid_changed', $callback);\n";
	}

	if ($config["map_dragend"] != "") {
		$callback = $config['map_dragend'];
		echo "google.maps.event.addListener(map, 'dragend', $callback);\n";
	}

	if ($config["map_zoom"] != "") {
		$callback = $config['map_zoom'];
		echo "google.maps.event.addListener(map, 'zoom_changed', $callback);\n";
	}
	if ($config["onLoad"] != "") {
		$callback = $config['onLoad'];
		echo "$callback;\n";
	}
}


/**
 * Inserts map script where called.
 *
 * Outputs the javascript function that implements the loading of the map into the page.
 * It is necesary to have loaded jQuery for working.
 *
 * @since 2.0
 *
 * @param mixed $config Array of options to configure the way it shows the map and a
 *                                      callback to execute after the loading of the map, an the loading of the scripts.
 *
 */
function insertMapScript($config) {
	$defaults = Array(
	    'positionable_marker' => 'true',
	    'container' => 'map',
	    'map_click' => '',
	    'map_dragend' => "",
	    'mapType_Changed' =>"",
	    'map_zoom' => '',
	    'originLat' => "0",
	    'originLong' => "0",
	    'zoom' => "0",
	    'type' => 'ROADMAP',
	    'originRadius' => "0",
	    'onLoad' => ''
	);
	$config = wp_parse_args((array) $config, $defaults);

	printGoogleApi();

	echo "<script language=\"JavaScript\">\n";

	printMapScript($config['container'], $config['originLat'], $config['originLong'], $config['zoom'], $config['type']);

	if ($config['positionable_marker']) {
		echo "var marker;\n";
		echo "var circle;\n";
	}

	echo "function after_loading(){\n";

	printMapEvents($config);

	if ($config['positionable_marker']) {
		printPositionableMarkerScript($config['originLat'], $config['originLong'], $config['originRadius']);
	}

	echo "};\n";
	echo "</script>";
}


/**
 * Prints the script for a positionable marker
 *
 * Prints the script for a positionable marker
 *
 * @since 2.0
 *
  @param mixed $config Array of options to configure the way it shows the map and a
 *                                      callback to execute after the loading of the map, an the loading of the scripts.
 *
 */
function printPositionableMarkerScript($lat, $long, $radius) {
	echo "markerLatLong = new google.maps.LatLng($lat,$long);\n";
	echo "marker=new google.maps.Marker({\n";
	echo "  position: markerLatLong,\n";
	echo "  map:map,\n";
	echo "  draggable: true\n";
	echo "});\n";
	echo "circle = new google.maps.Circle({\n";
	echo "	center: markerLatLong,\n";
	echo "	map:map,\n";
	echo "	clickable:true,\n";
	echo "	fillOpacity:0.4,\n";
	echo "	fillColor:\"#00AAFF\",\n";
	echo "	strokeColor:\"#00AAFF\",\n";
	echo "	strokeOpacity:0.9,\n";
	echo "	radius: $radius\n";
	echo "});\n";
	echo "circle.bindTo(\"center\",marker,\"position\");\n";
	echo "google.maps.event.addListenerOnce(circle, 'click', resizeCircle);\n";
}

?>
