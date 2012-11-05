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

if (!class_exists('Areago')){
	
	class Areago{
		
		function areago_init (){
				
		}// areago_init
		
		function areago_generate_rewrite_rules(){

			global $wp_rewrite;				
			$newrules = array();
			$newrules['areago/listado'] = 'wp-content/plugins/areago-plugin/prueba.php';
			$newrules['areago/prueba'] = 'wp-content/plugins/areago-plugin/prueba.php';				
 			$wp_rewrite->non_wp_rules = $newrules + $wp_rewrite->non_wp_rules;
					
		}//areago_generate_rewrite_rules
		
		
	}
	
	
}


$areago_plugin = new Areago();

register_activation_hook( __FILE__, 'areago_install' );



function areago_install(){
	
	global $wp_rewrite;
	$newrules = array();
	$newrules['areago/listado'] = 'wp-content/plugins/areago-plugin/prueba.php';
	$newrules['areago/descarga'] = 'wp-content/plugins/areago-plugin/prueba.php';
	$wp_rewrite->non_wp_rules = $newrules + $wp_rewrite->non_wp_rules;
		
	flush_rewrite_rules(true);
	
}



add_action("init", array($areago_plugin , "areago_init"));
add_filter( 'generate_rewrite_rules',array($areago_plugin, 'areago_generate_rewrite_rules' ));


/*


*/
