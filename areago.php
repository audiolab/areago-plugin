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
			add_options_page(__('Areago','areago'), __('Areago','areago'), 'manage_options', 'areago-options-menu',array($this,'areago_menu_page_callback'));				
		}// areago_admin_menu
		
		function areago_menu_page_callback(){

			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			

			
			
			
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
	
}// areago_install



add_action("init", array($areago_plugin , "areago_init"));
add_filter( 'generate_rewrite_rules',array($areago_plugin, 'areago_generate_rewrite_rules' ));
