<?php


if (!class_exists('Areago_DB_Helper')){

	class Areago_DB_Helper{
		
		const TABLE_NAME ="areago";
		
		function install(){
			
			global $wpdb;
				
			$table_name = $wpdb->prefix . self::TABLE_NAME;
			
			
			$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
			name tinytext NOT NULL,
			excerpt tinytext NOT NULL,
			description text NOT NULL,
			recordings int DEFAULT 0 NOT NULL,
			language tinytext NOT NULL,
			size int DEFAULT 0 NOT NULL,
			reference longtext DEFAULT '',
			points longtext DEFAULT '',
			hash tinytext DEFAULT '',
			UNIQUE KEY id (id)
			);";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
		}
		
		function get_array_walks(){
			
			global $wpdb;
			$table_name = $wpdb->prefix . self::TABLE_NAME;
			
			$sql = "SELECT * FROM $table_name";
			$res = $wpdb->get_results($sql,ARRAY_A);
			return $res;
			
		}
		
		function get_list_walks(){
			$walks = $this->get_array_walks();
			if ($walks==NULL)
				return NULL;
			
			$r = array();			
			foreach ($walks as $walk){
				
				$o = new stdClass();
				$o->id = $walk['id'];
				$o->nombre = $walk['name'];
				$o->resumen = $walk['excerpt'];
				$o->grabaciones = $walk['recordings'];
				$o->idioma = $walk['language'];
				$o->hash = $walk['hash'];
				$o->referencia = json_decode($walk['reference']);
				$r[] = $o;
			}
			
			return $r;			
			
		}// get_list_walks
		
		function get_walk($id){
			global $wpdb;
			$table_name = $wpdb->prefix . self::TABLE_NAME;				
			$sql = "SELECT * FROM $table_name WHERE id = '$id'";
			$res = $wpdb->get_row($wpdb->prepare($sql),OBJECT);
			return $res;
		}// get_walk
		
		function save_walk(){
			//guardar el paseo. Si ya existe, lo actualiza.
			$update = false;
			global $wpdb;
			
			$table_name = $wpdb->prefix . self::TABLE_NAME;
			$sql = "INSERT INTO $table_name";
			//Primero chequeo que de verdad hay que guardar
			if( isset($_POST[ 'areago-form-add' ]) && $_POST[ 'areago-form-add' ] == 'Y' ) {
				$titulo = $_POST['areago_title']; //T’tulo del paseo
				$descripcion = $_POST['areago_description']; //Descripci—n del paseo
				$language = $_POST['areago_language'];
				$puntos =stripslashes( $_POST['areago_points']);	//Puntos del paseo
				$excerpt = $_POST['areago_excerpt'];
				$puntosOBJ = json_decode($puntos);
				if (isset($_POST[ 'areago_walk_id' ]) && $_POST['areago_walk_id']!= ''){
					$update = true;
					$sql = "UPDATE ";
				}
								
				$recordings = count($puntosOBJ);
				$hash = md5(uniqid(rand(), TRUE));
				$sql .= "(name, excerpt, description, recordings, language, points, hash)";
				$sql .= "VALUES (%s, %s, %s, %d, %s, %s, %s)";
				
				$res = $wpdb->query( $wpdb->prepare($sql,
						$titulo,
						$excerpt,
						$descripcion,
						$recordings,
						$language,
						$puntos,
						$hash
				) );
				if ($res){
					$result_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE hash='$hash'" ));
					return $result_id;
					
				}else{
					return FALSE;
				}//$res
				
			}
				
		}
		
	}//class Areago_DB_Helper
	
	
}