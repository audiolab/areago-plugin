<?php
/*
Plugin Name: Areago Web Interface
Plugin URI: http://www.audio-lab.org
Description: Plugin para la gesti—n de audioguias basado en el software Areago.
Version: 1.0
Author: Xavier Balderas
Author URI: http://www.audio-lab.org
License: GPL2
*/


// TODO checkear que se puede escribir el doc .htaccess para a–adir los rewrites...
 

require_once (dirname( __FILE__ ) . '/libs/db_helper.php');
require_once (dirname( __FILE__ ) . '/libs/classes.php');


if (!class_exists('Areago')){
	
	class Areago{
		
		function areago_init (){
			add_image_size('areago_picture', 512, 512, true);
		}// areago_init
		
		function areago_generate_rewrite_rules(){

			global $wp_rewrite;				
			$newrules = array();
			$newrules['areago/listado'] = 'wp-content/plugins/areago-plugin/prueba.php';
			$newrules['areago/descarga/(\d*)$'] = 'wp-content/plugins/areago-plugin/descarga.php?paseo=$1';				
 			$wp_rewrite->non_wp_rules = $newrules + $wp_rewrite->non_wp_rules;
					
		}//areago_generate_rewrite_rules
		
		function register_actions(){
			add_action('admin_menu',array($this, 'areago_admin_menu'));
			add_action('wp_ajax_areago_get_marker',array($this,'areago_ajax_get_marker'));
			add_filter('media_send_to_editor', array($this, 'areago_media_send_to_editor'), 50, 3);
		}
		
		function areago_ajax_get_marker(){
			if (!isset($_POST['id'])){
				die();
			}
			if (!class_exists('Soundmap_Helper')){
			
				die('Soundmap Plugin is needed');
			}

			$sm_helper = new Soundmap_Helper();
			$marker = $sm_helper->get_marker($_POST['id']);
			if ($marker){
				$rt['ID'] = $marker->ID;
				$rt['title'] = $marker->post_title;
				$rt['marker'] = $marker->marker;
				//var_dump($marker);
				echo json_encode($rt);				
			}
			die();
			
		}
		
		function areago_admin_menu(){
			add_menu_page( 'List Walks', 'Areago', 'manage_options', 'areago-manage', array($this,'areago_menu_page_callback'));
			$add_page = add_submenu_page( "areago-manage", "Add walk", "Add walk", 'manage_options', 'areago-manage-add', array($this, 'areago_manage_add') );
			
			add_action('admin_enqueue_scripts', array($this, 'areago_admin_enqueue_scripts'));
			
		}// areago_admin_menu
		
		function areago_admin_enqueue_scripts($hook){
			if ($hook=='areago_page_areago-manage-add') {
				//Styles and scripts for the add/edit page.
				
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script('jquery_button');
				wp_enqueue_script('jquery-ui-slider');
				wp_enqueue_script('thickbox');
				wp_enqueue_script('media-upload');
				//wp_enqueue_script('prototype');
				//wp_enqueue_script('jquery_dialog');
				wp_enqueue_script('json2');
				
				wp_enqueue_style('areago_add_page_css', plugins_url('css/areago-add.css', __FILE__),array(), '1.0.2', 'all');
				wp_enqueue_style('areago_interface_css', plugins_url('css/areago-interface.css', __FILE__),array(), '1.0.2', 'all');
				wp_enqueue_style('thickbox');
				//wp_enqueue_style('areago_interface_css', 'http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css',array(), '1.0.2', 'all');
				
				wp_enqueue_script( 'areago_openlayers', plugins_url('js/openlayers/OpenLayers.debug.js', __FILE__) );
				wp_enqueue_script( 'areago_prototype', plugins_url('js/prototype.js', __FILE__) );
				//wp_enqueue_script( 'classy', plugins_url('js/classy.js', __FILE__) );
				//http://maps.google.com/maps/api/js?v=3.7&amp;sensor=false
				//http://maps.google.com/maps?file=api&amp;v=2&amp;key=AIzaSyAgkij6iwi66yV384I4BB-aKNbvWm5FKMQ
				wp_enqueue_script( 'areago_google', 'http://maps.google.com/maps/api/js?v=3.7&amp;sensor=false' );				
				
				wp_enqueue_script( 'areago_datatables', plugins_url('js/jquery.datatables/jquery.dataTables.js', __FILE__) );
				wp_enqueue_script( 'areago_jplayer', plugins_url('js/jquery.jplayer/jquery.jplayer.min.js', __FILE__) );
				wp_enqueue_script( 'areago_admin', plugins_url('js/areago.admin.js', __FILE__) );
				
				global $soundmap;
				
				$params = array();				
				$params += $soundmap['origin'];
				$params ['mapType'] = $soundmap['mapType'];				
				
				wp_localize_script('areago_admin','AreagoOptions',$params);
								
				
			}
		}
		
		function areago_media_send_to_editor($html, $attachment_id, $attachment) {
			// check for the GET vars added in boxView
			parse_str($_POST['_wp_http_referer'], $aPostVars);
			if (isset($aPostVars['target'])&&$aPostVars['target']=='areago-picture' && wp_attachment_is_image($attachment_id)) {
				// add extra data to the $attachement array prior to returning the json_encoded string
				$attachment['id'] = $attachment_id;
				$attachment['edit'] = get_edit_post_link($attachment_id);
				$attachment['input'] = $aPostVars['input'];
				$attachment['preview'] = $aPostVars['preview'];
				$aImg = wp_get_attachment_image_src( $attachment_id, 'thumb');
				if ($aImg) $attachment['img_thumb'] = $aImg[0];
				$html = json_encode($attachment);
			}
			return $html;
		}
		
		function areago_manage_add(){
			
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
				
			if (!class_exists('Soundmap_Helper')){
				
				wp_die('Soundmap Plugin is needed');
				
			}
			
			$sm_helper = new Soundmap_Helper();
			$markers = $sm_helper->get_all_markers();		
			if( isset($_POST[ 'areago-form-add' ]) && $_POST[ 'areago-form-add' ] == 'Y' ) {
				//Tenemos datos, por lo que hay que guardarlos...	
				$this->areago_save_walk();
			}
			
			?>			
			
			<div class="wrap">			
			<div id="icon-post" class="icon32"><br/></div>
			<h2>Add New Walk</h2>
			   
			        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			        <form id="areago-new-walk" method="POST">
			        <div id="poststuff">
			            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
						            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
						            <div id="post-body" class="metabox-holder columns-2">
						            	<div class="postbox-container" id="postbox-container-1">
						            	
						        			<div id="lateral-sortables" class="meta-box-sortables ui-sortable">
						        				<div id="language" class="postbox">
						        					<div class="handlediv" title="Click to toggle">
						        						<br>
						        					</div>
						        					<h3 class="hndle"><span>Language</span></h3>
						        					<div class="inside">						        					
						        						<label class="screen-reader-text" for="areago-language">Language</label>
						        						<input name="areago_language" id="areago-language" type="text" tabindex="2" size="10" autocomplete="on"/>
						        					</div>
						        					
						        				</div><!-- language -->
						        				<div id="areago-picture-wrapper" class="postbox">
						        					<div class="handlediv" title="Click to toggle">
						        						<br>
						        					</div>
						        					<h3 class="hndle"><span>Picture</span></h3>
						        					<div class="inside">
						        					<?php $sUri = esc_url( str_replace('&type=','&target=areago-picture&input=areago-picture&preview=areago-picture-preview&tab=library&type=',get_upload_iframe_src('image')) );?>						        					
						        						<p>
														<?php echo '<a title="Imagenes" href="'.$sUri.'" class="thickbox button image" data-type="image">Choose Image</a> '; ?>
														</p>
														<div id="areago-picture-preview">
														</div>
						        						<input name="areago_picture" id="areago-picture" type="hidden" />
						        					</div>						        					
						        					
						        				</div><!-- language -->
						        				<div id="areago-actions" class="postbox">
						        					<div class="handlediv" title="Click to toggle">
						        						<br>
						        					</div>
						        					<h3 class="hndle"><span>Actions</span></h3>
						        					<div class="inside">
						        					<p class="submit">
														<input type="submit" name="Submit" class="button-primary" id="areago-submit" value="<?php esc_attr_e('Save Changes') ?>" />
													</p> 
						        					</div>						        					
						        					
						        				</div><!-- areago-actions -->
						        									
						        			</div><!-- lateral-sortables  --> 
						        									            	
						            	</div><!-- postbox-container-1 -->
							            <div class="postbox-container" id="postbox-container-2">
							            	<div id="titlediv">
							            		<div id="titlewrap">
							            			<label class="hide-if-no-js" id="title-prompt-text" for="title">Title</label>
							            			<input type="text" name="areago_title" size="30" tabindex="1" id="title" autocomplete="off" />
						    	        		</div><!-- titlewrap -->
						        			</div><!-- titlediv --> 
						        			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						        				<div id="areago-excerpt-box" class="postbox">
						        					<div class="handlediv" title="Click to toggle">
						        						<br>
						        					</div>
						        					<h3 class="hndle"><span>Excerpt</span></h3>
						        					<div class="inside">
						        						<label class="screen-reader-text" for="aregao_excerpt">Excerpt</label>
														<textarea id="areago-excerpt" name="areago_excerpt"></textarea>
						        					</div>
						        				</div><!-- excerpt -->
						        				<div id="description" class="postbox">
						        					<div class="handlediv" title="Click to toggle">
						        						<br>
						        					</div>
						        					<h3 class="hndle"><span>Description</span></h3>
						        					<div class="inside">
						        						<label class="screen-reader-text" for="description">Description</label>
						        						<?php wp_editor("", "areago_description", array('media_buttons'=>false));?>						        						
						        					</div>
						        				</div><!-- description -->
						        				
						        								        				
						        			</div><!-- normal-sortables  -->   	
						            	</div> <!-- postbox-container-2 -->
						            </div> <!-- post-body -->
						            <div class="clear"></div>
						        	<div id="mapa-body" class="metabox-holder">
						        				<div id="mapa" class="postbox">
						        					<div class="handlediv" title="Click to toggle">
						        						<br>
						        					</div>
						        					<h3 class="hndle"><span>Map</span></h3>
						        					<div class="inside">		
						        						<div id="areago-toolbar">
						        							<ul id="areago-add-menu">	
						        								<li><a id="areago-new-play_once" href="#">P1</a></li>
							        							<li><a id="areago-new-play_loop" href="#">PL</a></li>
							        							<li><a id="areago-new-play_finish" href="#">PF</a></li>
							        							<li><a id="areago-new-toogle" href="#">T</a></li>
							        							<li><a id="areago-new-conditional" href="#">C</a></li>
							        							<li><a id="areago-new-wifi" href="#">W</a></li>
						        							</ul>	
						        						
						        						</div> <!-- toolbar -->				        						
					        							<div id="map-holder">

					        							<div id="map">
						        						
						        						</div><!-- map -->					        							
					        							</div>
						        							
	
						        						<div id="marker-editor">
						        							<div id="marker-editor-holder" class="panel_A">
								        						<div id="markers-table">
									        						<p>Add the markers for your walk. Click to add a point.</p>
									        							<?php 
									        							if ($markers!=false){
																			echo "<table>";
																			echo '<thead><tr><th>ID</th><th>Title</th><th>Latitude</th><th>Longitude</th><th>Author</th></tr></thead>';
																			foreach ($markers as $mark){
																				echo "<tr>";
																				echo '<td>' . $mark->ID . '</td>';
																				echo '<td>' . $mark->post_title . '</td>';																	
																				echo '<td>' . $mark->marker['lat'][0] . '</td>';
																				echo '<td>' . $mark->marker['lng'][0] . '</td>';
																				echo '<td>' . $mark->marker['author'][0] . '</td>';
																				echo '</tr>';
																			}//foreach
																				echo '</table>';
																		}//if
									        												
									        							?>
								        							
								        						</div><!-- markers-table -->
								        						<div id="areago-panel_A-marker-info" class="markers-editor-properties">
							        							<h2><span id="marker-title">TITULO</span></h2>
							        							<div id="marker-sound">
							        									<div id="jquery_jplayer_1" class="jp-jplayer"></div>
																		<div id="jp_container_1">
																			<div class="jp-gui ui-widget ui-widget-content ui-corner-all">
																				<ul>
																					<li class="jp-play ui-state-default ui-corner-all"><a href="javascript:;" class="jp-play ui-icon ui-icon-play" tabindex="1" title="play">play</a></li>
																					<li class="jp-pause ui-state-default ui-corner-all"><a href="javascript:;" class="jp-pause ui-icon ui-icon-pause" tabindex="1" title="pause">pause</a></li>
																					<li class="jp-stop ui-state-default ui-corner-all"><a href="javascript:;" class="jp-stop ui-icon ui-icon-stop" tabindex="1" title="stop">stop</a></li>
																				</ul>
																				<div class="jp-progress-slider"></div>
																				<div class="jp-clearboth"></div>
																			</div>
																			<div class="jp-no-solution">
																				<span>Update Required</span>
																				To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
																			</div>
																		</div><!-- jp_container_1 -->
							        							
							        							</div><!-- marker-sound -->
							        							<p><button id="edit-position">Edit position</button></p>
							        							<p>
							        								<strong>Radius: </strong><span id="marker-radius"></span>							        								
							        								<p><button id="edit-radius">Edit radius</button></p>
							        							</p>
							        							</div><!-- areago-panel_A-marker-info -->
							        							<div id="areago-point-fade">
							        								<p><input type="checkbox" id="areago-fade" name="auto-fade" value="auto_fade"><label for="areago-fade">Auto Fade</label></p>
							        							</div>
							        							<div id="areago-point-actions">
								        							<hr>
								        							<p><input type="checkbox" id="areago-reference" name="reference-point" value="areago_reference"><label for="areago-reference">Define this point as reference point</label>
								        							<div id="areago-message" class="message"><p>Prueba de mensaje</p></div>
								        							<p><button id="areago-save-point">Save point</button></p>
								        						</div> <!-- areago-point-actions -->
						        							</div><!-- marker-editor-holder -->
						        						</div><!-- marker-editor -->
						        						
						        						<div class="clear"></div>	        						
						        					</div><!-- inside -->
						        					<div id="areago-dialog-confirm" title="Confirm action">
													    <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>Do you want to save the actual point?</p>
													    <input type="hidden" class="type" value="">
													</div> <!-- areago-dialog-confirm -->
						        				</div><!-- mapa -->		
						            </div>
						            
					</div> <!--  poststuff -->	         
					
					<input type="hidden" id="areago-points" value="" name="areago_points"/>
					<input type="hidden" id="areago-walk-id" value="" name="areago_walk_id"/>
					<input type="hidden" name="areago-form-add" value="Y"/>
  
					</form>
						        
						    </div>
						    <div id="console">
						    </div>
			<?php
		}
		
		function areago_menu_page_callback(){
			
			
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			if( isset($_GET[ 'action' ]) && $_GET[ 'action' ] == 'delete' ) {
				//Tenemos datos, por lo que hay que guardarlos...
				$id = $_GET['walk'];
				
				$db_helper = new Areago_DB_Helper();
				$db_helper->delete_walk($id);
			}
			
			$table = new Areago_Paseos_List_Table();
			$table->prepare_items();
			?>
			    <div class="wrap">
			        
			        <div id="icon-users" class="icon32"><br/></div>
			        <h2>Walks <a href="<?php echo sprintf('?page=%s&action=%s"',$_REQUEST['page'],'add')?>" class="add-new-h2">Add walk</a></h2>			       
			        
			        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			        <form id="areago-lists" method="get">
			        <style>
			        #areago-lists #excerpt{
						width:auto;
					}
			        </style>
			            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
			            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			            <!-- Now we can render the completed list table -->
			            <?php $table->display() ?>
			            
			            
			        </form>
			        
			    </div>
			    <?php

			
			
			
			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
			/*if (isset($_POST['soundmap_op_noncename'])){
				if ( !wp_verify_nonce( $_POST['soundmap_op_noncename'], plugin_basename( __FILE__ ) ) )
					return;
				_soundmap_save_options();
			}*/
		}// areago_menu_page_callback
		
		function areago_save_walk(){
			
			$db_helper = new Areago_DB_Helper();
			$id = $db_helper->save_walk();
			if ($id === FALSE)
				wp_die("Fallo al escribir en la base de datos");
			
			$file_helper = new Areago_File_Helper();
			$created = $file_helper->createFolder($id);
			
			if (!$created)
				wp_die("Fallo al intentar crear la carpeta");
			
			$zip_helper = new Areago_ZIP();
			$zip_helper->create_zip($id);

		}//function areago_save_walk		
		
	}// class Areago
	
	
}


$areago_plugin = new Areago();
$areago_plugin->register_actions();

register_activation_hook( __FILE__, 'areago_install' );



function areago_install(){
	
	global $wp_rewrite;
	$newrules = array();
	$newrules['areago/listado'] = 'wp-content/plugins/areago-plugin/listado.php';
	$newrules['areago/descarga/(\d*)$'] = 'wp-content/plugins/areago-plugin/descarga.php?paseo=$1';				
	$wp_rewrite->non_wp_rules = $newrules + $wp_rewrite->non_wp_rules;
		
	flush_rewrite_rules(true);
	
	//Install the db.
	$db_helper = new Areago_DB_Helper();
	$db_helper->install();
	
	
	$file_helper = new Areago_File_Helper();
	$fileOK = $file_helper->check();
	
}// areago_install



add_action("init", array($areago_plugin , "areago_init"));
add_filter( 'generate_rewrite_rules',array($areago_plugin, 'areago_generate_rewrite_rules' ));
