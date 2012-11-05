<?php

/** Sets up the WordPress Environment. */

require( '../../../../wp-load.php' );
//nocache_headers();
session_start();
/** End Set up de thordpress Environment. **/
load_plugin_textdomain('soinumapa',false,'SoinuMapa_for_Wordpress/phpLib');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
<?php global $SoinuMapa;
?>
<link rel="stylesheet" href="<?php echo SOINUMAPA_URL  ?>/phpLib/css/reset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo SOINUMAPA_URL  ?>/phpLib/css/text.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo SOINUMAPA_URL  ?>/phpLib/css/960.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo SOINUMAPA_URL  ?>/phpLib/css/style.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo SOINUMAPA_URL  ?>/phpLib/css/jquery-ui-1.7.3.custom.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo SOINUMAPA_URL  ?>/phpLib/css/jquery.jgrowl.css" type="text/css"/>
<link rel="stylesheet" type="text/css" href="<?php echo SOINUMAPA_URL  ?>/phpLib/css/jquery.autocomplete.css" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/swfupload/swfupload.js"></script>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/js/upload.js"></script>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/js/fileprogress.js"></script>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/js/handlers.js"></script>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/js/jquery.infieldlabel.min.js"></script>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/js/jquery-ui-1.7.3.custom.min.js"></script>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/js/jquery.autocomplete_geomod.js"></script>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/js/geo_autocomplete.js"></script>
<script type="text/javascript" src="<?php echo SOINUMAPA_URL  ?>/phpLib/js/jquery.jgrowl_minimized.js"></script>

<?php 
    $config= Array(
        'positionable_marker'=>true,
        'container'=>'map',
        'map_click'=>'onMapClick',
    );
	insertMap($config);
?>

<script type="text/javascript">
var swfu;
var url_soinumapa="<?php echo SOINUMAPA_URL ?>";
window.onload = function() {	
	var settings = {
			flash_url : "<?php echo SOINUMAPA_URL  ?>/phpLib/swfupload/swfupload.swf",
			flash9_url : "<?php echo SOINUMAPA_URL  ?>/phpLib/swfupload/swfupload_fp9.swf",
			upload_url: "<?php echo SOINUMAPA_URL  ?>/phpLib/file-handler.php",
			post_params: {"PHPSESSID" : "<?php echo session_id(); ?>"},

			// File Upload Settings
			file_size_limit : "32 MB",	// 2MB
			file_types : "*.mp3",
			file_types_description : "MP3 Audio Files",
			file_upload_limit : 0,
			
			custom_settings : {
				upload_target : "divFileProgressContainer"
			},
			debug: false,

			// Button settings
			button_width: "160",
			button_height: "30",
			button_placeholder_id: "spanButtonPlaceHolder",
			button_text: '<span class="uploadButtonText"><?php _e("Select file","soinumapa")?></span>',
			button_text_style: '.uploadButtonText { color:#ffffff; font-size: 14pt; font-family:"Myriad Pro","Helvetica Neue",Arial,"Liberation Sans",FreeSans,sans-serif}',
			button_text_left_padding: 15,
			button_text_top_padding: 6,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,
			
			// Event Handler Settings - these functions as defined in Handlers.js
			//  The handlers are not part of SWFUpload but are part of my website and control how
			//  my website reacts to the SWFUpload events.
			swfupload_preload_handler : preLoad,
			swfupload_load_failed_handler : loadFailed,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
		};

		swfu = new SWFUpload(settings);
};
</script>
</head>
<body>
	<div id="page">
		<div class="container_16">
			<div class="content">
				<div class="grid_12 alpha omega" id="main">
					<div class="grid_8 alpha" id="dataColumn">
						<div class="grid_8 bloque " id="paso1">
							<div class="grid_1 alpha paso pasoActivo" id="uno"><h3>1</h3></div>
							<div class="grid_7 alpha" id="archivo">
								<form id="fileUpload">
									<div id="error_1" class="error">
										<?php _e("You must upload a file.","soinumapa")?>
									</div>
							
									<div id="uploadButton" class="grid_3 alpha">
										<span id="spanButtonPlaceHolder"></span>
									</div>
									<div id="divFileProgressContainer" class="grid_3 omega" style="height:30px;"></div>
								</form>
									<div class="grid_7 alpha omega" id="recordingInfo">
									<hr class="grid_7 alpha"></hr>
										<h5><?php _e("Recording data:","soinumapa")?></h5>
										<input type="hidden" id="fileURL" name="fileURL" value=""></input>
										<input type="hidden" id="fileDir" name="fileDir" value=""></input>
										<input type="hidden" id="duracion" name="duracion" value=""></input>
										<input type="hidden" id="fileName" name="fileName" value=""></input> 
									</div>
							
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
						<form id="upload-form">
						<div class="grid_8 bloque" id="paso2">
							<div class="grid_1 alpha paso" id="tres"><h3>2</h3></div>
							<div class="grid_7 alpha" id="posicion">
							<div id="error_2" class="error">
										hfdhjskfhsdkj
							</div>
							<form>
								<p><label for="direccion" class="etiqueta"><?php _e("Direction","soinumapa") ?></label><br/><input class="textInput grid_6 alpha" type="text" id="direccion" name="direccion" value=""></input><input type="button" value="Buscar" onclick="codeAddress()"></p>
								<div id="map" class="grid_7 alpha"></div>
								<input type="hidden" id="posLat" name="posLat" value="<?php echo $SoinuMapa->options->originLat?>"></input>
								<input type="hidden" id="posLong" name="posLong" value="<?php echo $SoinuMapa->options->originLong?>"></input>
							</form>
							</div>
						</div>
						<div class="clear"></div>
						<div class="grid_8 bloque" id="paso3">
							<div class="grid_1 alpha paso" id="cuatro"><h3>3</h3></div>
							<div class="grid_7 alpha omega" id="texto">
							<div id="error_3" class="error">
										<?php _e("At least, you have to introduce a title","soinumapa")?>
							</div>
							<div class="grid_7 alpha omega">
							
								<div id="error_4" class="error">	
										<?php _e("Please, enter the author","soinumapa")?>
								</div>
								<p><label for="autor" class="etiqueta"><?php _e("Author","soinumapa")?></label><br/><input class="textInput grid_6 alpha" type="text" id="autor" name="autor" value=""></input></p>
								<div class="clear"></div>
								<div id="error_5" class="error">	
										<?php _e("Please, enter the date","soinumapa")?>
								</div>
								<p><label for="fecha" class="etiqueta"><?php _e("Date (dd/mm/aaaa)","soinumapa")?></label><br/><input class="grid_4 alpha"type="text" id="fecha" name="fecha"></input></p>
							</div>
							<div class="clear"></div>
							<br/>
								<br/>
								<div class="grid_7 alpha omega" id="tabs">
									<ul>
										<li><a href="#info_eu">Euskara</a></li>
										<li><a href="#info_en">English</a></li>
										<li><a href="#info_es">Castellano</a></li>
									</ul>
								<div id="info_eu">
									<p class="grid_5"><label class="grid_1" for="titulo_eu" >Izenburua</label><input class="grid_6" type="text" id="titulo_eu" name="titulo_eu" value=""></input></p>
									<div class="clear"></div>
									<p class="grid_5"><label class="grid_1" for="descripcion_eu" >Azalpena</label><textarea class="grid_6" rows="5" type="text" id="descripcion_eu" name="descripcion_eu"></textarea></p>
									
								</div>
								<div id="info_en">
									<p class="grid_5"><label class="grid_1" for="titulo_en" >Title</label><input class="grid_6" type="text" id="titulo_en" name="titulo_en" value=""></input></p>
									<div class="clear"></div>
									<p class="grid_5"><label class="grid_1" for="descripcion_en" >Description</label><textarea class="grid_6" rows="5" type="text" id="descripcion_en" name="descripcion_en"></textarea></p>
									
								</div>
								<div id="info_es">
									<p class="grid_5"><label class="grid_1" for="titulo_es" >Título</label><input class="grid_6" type="text" id="titulo_es" name="titulo_es" value=""></input></p>
									<div class="clear"></div>
									<p class="grid_5"><label class="grid_1" for="descripcion_es" >Descripción</label><textarea class="grid_6" rows="5" type="text" id="descripcion_es" name="descripcion_es"></textarea></p>
									
								</div>
							</div>
						

						</div>
						</div>
						<div class="clear"></div>
						<div class="grid_8 bloque" id="paso4">
							<div class="grid_1 alpha paso" id="cinco"><h3>4</h3></div>
							<div class="grid_7 omega" id="tags">
								<div id="error_6" class="error">
										<?php _e("It is necessary to select one category.","soinumapa")?>
								</div>
								<p><label for="categoria"><?php _e("Category","soinumapa")?></label></p>
								<p><select id="categoria" name="categoria" value="">
									                <?php
                								$categories=  get_categories();

                								foreach ($categories as $cat) {

                    							$option = '<option value="'.$cat->term_id.'">';
                    							$option .= $cat->cat_name;
                    							$option .= '</option>';
                    							echo $option;

								                }
                							?>
								</select></p>
							</div>
						</div>
						<div class="clear"></div>
						<div class="grid_8 bloque" id="paso5">
							<div class="grid_1 alpha paso" id="seis"><h3>5</h3></div>
							<div class="grid_7 omega" id="subir">
								<div id="error_7" class="error">
										<?php _e("The verification code is incorrect","soinumapa")?>
								</div>
  								<img class="captchaImg" src="<?php echo SOINUMAPA_URL  ?>/phpLib/captcha/securimage_show.php" alt="CAPTCHA Image" />
  								<p><label for="captcha" class="etiqueta"><?php _e("Enter the text that appears on the image","soinumapa")?></label><br/><input class="textInput" type="text" id="captcha" name="captcha" value=""></input></p>
  								<button type="submit" id="submit"><?php _e("Save recording","soinumapa")?></button>
							</div>
						</div>
						</form>
					</div>
					<div class="grid_4 omega" id="infoColumn">
						<h2><?php _e('Colabora con Soinumapa',"soinumapa") ?></h2>
						<p><?php _e('Hello,',"soinumapa") ?></p>
						<p><?php _e('Upload your recording to Soinumapa.net. Just follow a few simple steps and within minutes the map of Soinumapa.net will show your recording..',"soinumapa") ?></p>
						<p><?php _e('The files must be original recordings in MP3 format.',"soinumapa") ?></p>
						<p><?php _e('Unless stated otherwise, your file, like all the sounds that make Soinumapa.net, will be published under a semi-free license (for more information see About Us section).',"soinumapa") ?></p>
						<p><?php _e('Please fill in as much information as possible to identify your recording, and locate it within the map as accurate as possible.',"soinumapa") ?></p>
						<p><?php _e('In any case, the editing team from Soinumapa reserves the right to correct or add certain information and / or choose to display or not some recordings, always following the objectives and working methods of the project.',"soinumapa") ?></p>
						<p><?php _e('Thanks for participating in Soinumapa.net!',"soinumapa") ?></p>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	<div id="savingContent">
		<div id="savingInfo">
			<h3><?php _e("Saving the recording","soinumapa")?></h3>
			<img src="<?php echo SOINUMAPA_URL?>/phpLib/images/loading.gif"></img>
		</div>
	</div>
</body>
</html>
