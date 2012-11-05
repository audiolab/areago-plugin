	
<?php

//require_once("PhpQuickProfiler.php");

//require_once("inicio.php");


//$pqp = new PQPExample();
//$pqp->init();

?>


<?php
	global $SoinuDroid;
	initializeSoundWalks();
	if ($SoinuDroid->count==0){
		$walks_exists=false;
	}else{
		$walks_exists=true;
	}
	
	
	
	
	global $sm_config;
	$originLat = $sm_config['options']["originLat"];
	$originLong = $sm_config['options']["originLong"];
	$zoom = $sm_config['options']["originZoom"];
	$mapType = $sm_config['options']["mapType"];
	$radius = $sm_config['options']['originRadius'];

	$config = Array(
	    'positionable_marker' => false,
	    'container' => 'map',	    	    	    
	    'originLat' => $originLat,
	    'originLong' => $originLong,
	    'zoom' => $zoom,
	    'type' => $mapType
	);
	insertMapScript($config);
	
	
	
	?>
<div class="wrap">
	<script>
		
	</script>

	<h2>SoinuDroid</h2>
	<div id="updated" class="updated">
	</div>
		<div id="tabla-paseos">
			<table id="paseos"></table>
		</div>
	<div id="walks-toolbar">
		<a id="icon-remove"></a>
		<a  id="icon-edit-walk"></a>
		<a  id="icon-download">
		<a  id="icon-add"></a>
		
	</div>
	<br>
		<div id="add-paseo">
			
			<div id="map" ></div>
			<div id="paseo-controles">
				<?php printAddWalk(); ?>
			</div>
			
		</div>
		<div id="clear"></div>
		
	
	<div id="hiddenPosts"></div>
	
</div>
<div id="console">
	
	
	
</div>


<?php

function printAddWalk(){
	?>
		

	<form method="post" action="" id="sound_walk_form">
		<div ><label><?php echo 'Nombre del paseo: ' ?></label></div><div ><input  type="text" name="walk_name" id="walk_name" value=""></div>		
		<div ><label><?php echo 'Descripcion: ' ?></label></div><div ><textarea  name="walk_description" id="walk_description" value=""></textarea></div>						
		<div id="tabla-marcadores">
			<table id="marcadores"></table>
		</div>
		<div id="tabla-marcadores-oculto">
			<table id="marcadores_oculto"></table>
		</div>
		<input type="hidden" name="soinudroid" value="soinumapa" />
		<input type="hidden" name="selectedMarkers" id="selectedMarkers" value=''/>
		<p><input name="save_soundwalk" type="submit" class="button-primary" value="<?php _e('Save & Export') ?>" /></p>
		
	</form>
	
	
	<?php
	
	
}
?>
