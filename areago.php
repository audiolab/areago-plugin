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
			add_submenu_page( "areago-manage", "Add walk", "Add walk", 'manage_options', 'areago-manage-add', array($this, 'areago_manage_add') );
			//add_options_page(__('Areago','areago'), __('Areago','areago'), 'manage_options', 'areago-options-menu',array($this,'areago_menu_page_callback'));				
		}// areago_admin_menu
		
		function areago_manage_add(){
			
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			?>
			
			<div class="wrap">
			 
			<div id="icon-post" class="icon32"><br/></div>
			<h2>Add New Walk</h2>
			   
			        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			        <form id="areago-new-walk" method="get">
			            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
						            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
						            <div class="metabox-holder">
						            	<div id="titlediv">
						            		<div id="titlewrap">
						            			<label class="hide-if-no-js" id="title-prompt-text" for="title">Title</label>
						            			<input type="text" name="areago_title" size="30" tabindex="1" id="title" autocomplete="off" />	
						            		</div>
						            	
						            	</div>
						            </div>
						            
						            
						            
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
