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
				//wp_enqueue_script('jquery_button');
				//wp_enqueue_script('jquery_dialog');
				
				wp_enqueue_style('areago_add_page_css', plugins_url('css/areago-add.css', __FILE__),array(), '1.0.2', 'all');
				wp_enqueue_style('areago_interface_css', plugins_url('css/areago-interface.css', __FILE__),array(), '1.0.2', 'all');
				
				
				//wp_enqueue_style('areago_interface_css', 'http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css',array(), '1.0.2', 'all');
				
				wp_enqueue_script( 'areago_openlayers', plugins_url('js/openlayers/OpenLayers.debug.js', __FILE__) );
								
				wp_enqueue_script( 'areago_google', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAjpkAC9ePGem0lIq5XcMiuhR_wWLPFku8Ix9i2SXYRVK3e45q1BQUd_beF8dtzKET_EteAjPdGDwqpQ' );				
				
				wp_enqueue_script( 'areago_datatables', plugins_url('js/jquery.datatables/jquery.dataTables.js', __FILE__) );
				
				wp_enqueue_script( 'areago_admin', plugins_url('js/areago.admin.js', __FILE__) );
				
				global $soundmap;
				
				$params = array();				
				$params += $soundmap['origin'];
				$params ['mapType'] = $soundmap['mapType'];				
				
				wp_localize_script('areago_admin','Areago',$params);
								
				
			}
		}
		
		function areago_manage_add(){
			
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
				
			if (!class_exists('Soundmap_Helper')){
				
				wp_die('Soundmap Plugin is needed');
				
			}
			
			$sm_helper = new Soundmap_Helper();
			
					
			?>
			
			
			<div class="wrap">
			 
			<div id="icon-post" class="icon32"><br/></div>
			<h2>Add New Walk</h2>
			   
			        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			        <form id="areago-new-walk" method="get">
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
						        						<label class="screen-reader-text" for="language">Language</label>
						        						<input name="areago_language" id="language" type="text" tabindex="2" size="10" autocomplete="on"/>
						        					</div>
						        				</div><!-- description -->
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
						        				<div id="description" class="postbox">
						        					<div class="handlediv" title="Click to toggle">
						        						<br>
						        					</div>
						        					<h3 class="hndle"><span>Description</span></h3>
						        					<div class="inside">
						        						<p>Insert an small description for the walk you are creating.</p>
						        						<label class="screen-reader-text" for="description">Description</label>
						        						<textarea rows="5" cols="40" name="areago_description" id="areago-description" tabindex="3"></textarea>
						        					</div>
						        				</div><!-- description -->
						        				
						        				<div id="mapa" class="postbox">
						        					<div class="handlediv" title="Click to toggle">
						        						<br>
						        					</div>
						        					<h3 class="hndle"><span>Map</span></h3>
						        					<div class="inside">						        						
						        											        				
						        												        						
						        						<div id="markers-table">
						        						<p>Add the markers for your walk. Make Doble-Click to add a point.</p>
						        							<?php $markers = $sm_helper->get_all_markers();
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
						        						<div id="map"></div><!-- map -->	
						        						<div class="clear"></div>	        						
						        					</div><!-- inside -->
						        				</div><!-- mapa -->		
						        								        				
						        			</div><!-- normal-sortables  -->   	
						            	</div> <!-- postbox-container-2 -->
						            </div> <!-- post-body -->
						            
						            
					</div> <!--  poststuff -->	            
					</form>
						        
						    </div>
			<?php
		}
		
		function areago_menu_page_callback(){
			
			
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			$table = new Areago_Paseos_List_Table();
			$table->prepare_items();
			?>
			    <div class="wrap">
			        
			        <div id="icon-users" class="icon32"><br/></div>
			        <h2>Walks <a href="<?php echo sprintf('?page=%s&action=%s"',$_REQUEST['page'],'add')?>" class="add-new-h2">Add walk</a></h2>			       
			        
			        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			        <form id="movies-filter" method="get">
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
		
		
	}// class Areago
	
	
}


$areago_plugin = new Areago();
$areago_plugin->register_actions();

register_activation_hook( __FILE__, 'areago_install' );



function areago_install(){
	
	global $wp_rewrite;
	$newrules = array();
	$newrules['areago/listado'] = 'wp-content/plugins/areago-plugin/prueba.php';
	$newrules['areago/descarga/(\d*)$'] = 'wp-content/plugins/areago-plugin/descarga.php?paseo=$1';				
	$wp_rewrite->non_wp_rules = $newrules + $wp_rewrite->non_wp_rules;
		
	flush_rewrite_rules(true);
	
	//Install the db.
	$db_helper = new Areago_DB_Helper();
	$db_helper->install();
	
	
}// areago_install



add_action("init", array($areago_plugin , "areago_init"));
add_filter( 'generate_rewrite_rules',array($areago_plugin, 'areago_generate_rewrite_rules' ));
