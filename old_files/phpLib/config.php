<?php

function default_options() {

	$r=Array(
	'originLat' => '0.0',
	'originLong' => '0.0',
	'originRadius' => '5',
	'originZoom' => '8',
	'mapType' => 'ROADMAP'
	);
	return $r;
}

function get_options() {

	$saved_options = get_option('sm_plugin_options');
	if ( empty( $saved_options ) ) {
		$r = default_options();
	}else{
		$r = maybe_unserialize( $saved_options );
	}
	return $r;
}

function save_options() {

	global $sm_config;

	$options_to_save = Array(
	'originLat' => $_POST['originLat'],
	'originLong' => $_POST['originLong'],
	'originZoom' => $_POST['zoom'],
	'originRadius' => $_POST['radius'],
	'mapType' => $_POST['mapType']
	    );

	update_option( 'sm_plugin_options' , maybe_serialize( $options_to_save ) );
	$sm_config["options"]=$options_to_save;
	unset ( $options_to_save, $sm_config );
	echo "Guardado";
}

$sm_config["mapTypes"] = Array(
    'Normal'=>"ROADMAP",
    'Satelite'=>"SATELLITE",
    "Hibrido"=>"HYBRID",
    'Relieve'=>"TERRAIN"
);

$sm_config["options"] = get_options();
?>
