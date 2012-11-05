<?php

/** Sets up the WordPress Environment. */
require( dirname(__FILE__) . '/wp-load.php' );

nocache_headers();

get_header();
session_start();

$plugins=get_option("active_plugins");
$more_languages=false;
if (in_array('qtranslate/qtranslate.php',$plugins)){
    global $q_config;
    $languages=get_option("qtranslate_enabled_languages");
    $languages_names=get_option("qtranslate_language_names");
    $more_languages=true;
    $lang_selected=$q_config["language"];

    $infotitle=newPostTitle();
    $infot=newPostMessage();
}
if($more_languages){
    $template_found=false;
    $temp="";
    $template=array();
    $title["es"]="Título";
    $title["en"]="Title";
    $title["eu"]="Izenburua";
  
    foreach($languages as $language){

        $temp.='<!--:' . $language . '-->TEMPLATE<!--:-->';
    }
    $dr='draft';
    $q="SELECT post_content FROM " . $wpdb->prefix . 'posts WHERE post_title="' . $temp . '" AND post_status="' . $dr . '";';
    $row=$wpdb->get_var($q);

    if ($row!=null){
        $split_regex = "#(<!--[^-]*-->|\[:[a-z]{2}\])#ism";
        $blocks = preg_split($split_regex, $row, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
        foreach ($blocks as $block){
            $si=preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches);
            $ssi= preg_match("#^<!--:-->$#ism", $block, $matches2);
            if($si){
                $current=$matches[1];
            }
            if(!($si or $ssi)){
                $template[$current]=$block;
                $template_found=true;
            }
        }
    }
}
?>

<div id="sidebar">
    <ul id="widget-sidebar">
        <?php 	/* Widgetized sidebar, if you have the plugin installed. */
        if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>

        <?php endif; ?>
    </ul>
</div>

<script type="text/javascript">
    var marker_new;
    var error_msg="";
    jQuery("document").ready(function(){

        GEvent.addListener(map,"click", function(overlay, latlng){
            marker_new.setLatLng(latlng);
            jQuery("#post_lat_text").val(latlng.lat());
            jQuery("#post_long_text").val(latlng.lng());
        });
        var origin_center=map.getCenter();
        marker_new=new GMarker(origin_center);
        map.addOverlay(marker_new);
        jQuery("#post_lat_text").val(origin_center.lat());
        jQuery("#post_long_text").val(origin_center.lng());
        jQuery("#submit-button").click(function(){
            jQuery("#uploading-div").css("display","block");
        });
        jQuery("#post-form").submit(function(){
            error_msg="";
            if (jQuery("#title-entry").val()==""){
                error_msg+=" Debe introducir título </br>";
            }
            if (jQuery("#tags-entry").val()==""){
                error_msg+="Debe introducir tags</br>";
            }
            if (jQuery("#archivo-entry").val()==""){
                error_msg+="Debe introducir archivo</br>";
            }
            if(error_msg!=""){
                jQuery("#error").html(error_msg);
                jQuery("#error").css("display","block");
                jQuery("#uploading-div").css("display","none");
                return false;
            }else{
                return true;
            }
        });


<?php
if ($more_languages){
    $selected=true;
    foreach($languages as $language){
        ?>
                jQuery("#<?php echo $language ?>").click(function(){

                    jQuery(".language_selected").attr("class","language");
                    jQuery("#language_<?php echo $language ?>").attr("class","language_selected");
                    jQuery(".entry_button_selected").attr("class","entry_button");
                    jQuery(this).attr("class","entry_button_selected");
                });
        <?php
    }
}

?>
    });
</script>
<?php
$status="";

function upload_file_post(){
    // obtenemos los datos del archivo
    global $status;
    global $_FILES;
 


    if ($_FILES["archivo"]["error"]==0){
      $tamano = $_FILES["archivo"]['size'];
      $tipo = $_FILES["archivo"]['type'];
      $archivo = $_FILES["archivo"]['name'];
      $prefijo = substr(md5(uniqid(rand())),0,6);
        if ($archivo != "") {
            // guardamos el archivo a la carpeta files
            $destino=WP_CONTENT_DIR . '/uploads/' . $prefijo."_".$archivo;
            $ext=wp_check_filetype($archivo);
            if ($ext['ext']=='mp3'){
                if (move_uploaded_file($_FILES['archivo']['tmp_name'],$destino)) {
                    chmod($destino, 0755);
                    $status = "Archivo subido";
                    $destino=WP_CONTENT_URL . '/uploads/' . $prefijo."_".$archivo;

                } else {
                    $status = "error en el comando copy";
                    $destino="error";
                }
            }else {
                $status="Extensión no válida (mp3)";
                $destino="error";
            }
        } else {
            $status = "error con la variable archivo";
            $destino="error";
        }
    }else{

        error_file_send_mail($_FILES["archivo"]["error"]);
        $destino="error";
    }

    return  $destino;

};

function error_file_send_mail($error_codigo){
    // multiple recipients
    $to  = 'xavibalderas@gmail.com' . ', '; // note the comma
    //$to .= 'xabierk@gmail.com';

    // subject
    $subject = 'Fallo al subir archivo en nuevo post';

    // message
    $message = "Se ha intentado subir un nuevo post, y ha ocurrido un fallo en el proceso de upload del archivo.\nError:\n";
    $message .= file_upload_error_message($error_codigo);
    // In case any of our lines are larger than 70 characters, we should use wordwrap()
    $message = wordwrap($message, 70);


    // Additional headers

    $headers .= 'From: Soinumapa Plugin <plugin@soinumapa.net>' . "\r\n";


    // Mail it
    mail($to, $subject, $message, $headers);
}

function file_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'El archivo a subir excede la directiva upload_max_filesize en php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'El archivo a subir excede la directiva MAX_FILE_SIZE especificada en el formulario HTML';
        case UPLOAD_ERR_PARTIAL:
            return 'El archivo fué parcialmente subido';
        case UPLOAD_ERR_NO_FILE:
            return 'No fué subido ningún archivo';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Falta una carpeta temporal';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Fallo al escribir el archivo en el disco';
        case UPLOAD_ERR_EXTENSION:
            return 'Subida de archivo parada por una extensión';
        default:
            return 'Error desconocido';
    }
}
function sm_addsoundfile($destino){

    global $wpdb;
    $t='INSERT INTO wp_xspf_player (url) VALUE ("'  . $destino . '");';
    $result= $wpdb->query($t);
    $orden=-1;
    if ($result){
        //We have added
        $num='SELECT id FROM wp_xspf_player WHERE url="' . $destino .'";';
        $orden=$wpdb->get_var($num);
        $pll='INSERT INTO wp_xspf_player_tracks_categories (idtrack,idcat) VALUES(' . $orden . ',1);';
        $wpdb->get_var($pll);
    }
    return $orden;
}
function sm_mount_post($orden){

    global $more_languages;
    global $languages;
    global $_POST;

    $content_post="";
    $title_post="";

    if ($more_languages){

        foreach($languages as $language){
            $content_post.='<!--:' . $language . '-->' . $_POST['post_Entry_' . $language] . '<br>[xspf]_start(TRUE, "order=' . $orden . '")[/xspf]<!--:-->';
            $title_post.='<!--:' . $language . '-->' . $_POST['post_title_' . $language] . '<!--:-->';
        }
    }else{
        $content_post=$_POST['post_Entry'];
        $title_post=$_POST['post_title'];
    }
    $my_post = array();
    $my_post['post_title'] = $title_post;
    $my_post['post_content'] = $content_post;
    $my_post['post_status'] = 'publish';
    $my_post['post_author'] = 1;
    $my_post['post_category'] = array($_POST['post_category']);
    $my_post['tags_input']=$_POST['post_tags'];

    return $my_post;
};

if (isset($_POST['SM_Add_NEW_POST'])){
    if($_SESSION['tmptxt'] == $_POST['tmptxt']){


        $destino=upload_file_post();
        $orden=-1;

        if ($destino!="error"){
            $orden=sm_addsoundfile($destino);
        }
        if ($orden!=-1){
            $post_content=sm_mount_post($orden);
            // Insert the post into the database
            $identific= wp_insert_post( $post_content );
            if ($identific!=0){
                $q = "INSERT INTO wp_sm_SoundPost (id,lat,lng) VALUES ";
                $q .= '(' . $identific ."," . $_POST['post_lat'] . ',' . $_POST['post_long'] . ');';
                $result= $wpdb->query($q);
                if ($result){
                    $status="¡Post saved!";
                }else{
                    $status.="Error al añadir el marcador";
                }

            }else{
                $status.="Error al crear el post";
            }

        }else{
            $status.="<br>Error al crear track";
        }
    }else{
        $status.="Error en el captcha";
    }
};

?>

<div>

    <div id="error" <?php if($status!=""){ echo "style=display:block";} ?>>
        <p><?php if($status!=""){ echo $status;} ?></p>

    </div>

    <div id="slide">

        <a id="slide-down"><img id="slide-up-img" src="<?php echo get_template_directory_uri() ?>/images/subir.png" /><img id="slide-down-img" src="<?php echo get_template_directory_uri() ?>/images/bajar.png" style="display:none"/><?php if ($more_languages){echo ($infotitle);} ?> : </a>
        <div id="slide-content" >
            <div  id="slide-999" class="slide-post-entry">
                <p> <?php
                    if ($more_languages){
                        echo ($infot);
                    }

                    ?></p>
            </div>
        </div>
    </div>

    <form id="post-form" enctype="multipart/form-data" method="post" action="wp-newpost.php">
        <div id="entry-text"><p>
                <label style="display:none">Entry: </label>
                <?php

                if ($more_languages){

                    $selected=true;
                    foreach ($languages as $language){
                        ?>
                <a class="entry_button<?php if ($selected){ echo '_selected'; $selected=false;} ?>" id="<?php echo $language ?>"><?php echo $languages_names[$language] ?></a>
                <?php
            }
            $selected=true;
            foreach ($languages as $language){
                ?><div class="language<?php if ($selected){ echo '_selected'; $selected=false;} ?>" id="language_<?php echo $language ?>">
                    <p> <label><?php echo  $title[$language]?> </label><input type="text" value="" id="title-entry_<?php echo $language ?>" name="post_title_<?php echo $language ?>"></p>
                    <textarea class="entry_area" id= "post_Entry_<?php echo $language ?>" name="post_Entry_<?php echo $language ?>"><?php if ($template_found){echo $template[$language];} ?></textarea>
                </div>
                <?php
            }
        }else{
            ?>
                <textarea name="post_Entry"></textarea>
                <?php
            }
            ?>


            </p>
        </div>
        <p style="display:none"><label>Lat: </label><input  id="post_lat_text" type="text" value="" size="20" name="post_lat" readonly="readonly" ></p>
        <p style="display:none"><label>Long: </label><input id="post_long_text" type="text" value="" size="20" name="post_long" readonly="readonly" ></p>
        <p><label>Tags:  </label><input type="text" value="" id="tags-entry" name="post_tags"></p>

        <p><label>Category: </label>
            <select name="post_category" >

                <?php
                $categories=  get_categories();

                foreach ($categories as $cat) {

                    $option = '<option value="'.$cat->term_id.'">';
                    $option .= $cat->cat_name;
                    $option .= '</option>';
                    echo $option;

                }
                ?>
            </select>
        </p>
        <p>
            <input name="archivo" id ="archivo-entry" type="file" size="20" />
            <input name="action" type="hidden" value="upload" style="display:none" />
        </p>
        <p>
            <img src="<?php  echo get_template_directory_uri(); ?>/captcha.php" width="100" height="30" vspace="3">
            
            <input name="tmptxt" type="text" size="5" >
        </p>

        <input class="submit" type="submit" value="Submit Post" tabindex="5" id="submit-button" name="SM_Add_NEW_POST"/>
    </form>

</div>

<div style="clear:both"></div>
<div id="footer">
    <!-- If you'd like to support WordPress, having the "powered by" link somewhere on your blog is the best way; it's our only promotion or advertising. -->
    <div id="footer-info">
        <hr />
        <p>
            <a href="<?php bloginfo('rss2_url'); ?>"><img src="<?php echo get_template_directory_uri() ?>/images/feed.png"></a> - <a href="<?php echo get_option('home') ?>/wp-admin"> Login</a> - <a href="wp-newpost.php" target="#blank">Upload</a>
        </p>
        <p><a href="<?php echo get_option('home'); ?>/">SOINUMAPA.NET 2005-09</a> / <a href="http://www.arteleku.net/audiolab" target="_blank">AUDIOLAB</a> / <a href="http://www.arteleku.net" target="_blank">ARTELEKU</a></p>
        <p><a href="http://wordpress.org/">WordPress</a> - SoinuMapa Plugin</p>
    </div>
</div>

</div>  <!-- content -->
</div>  <!-- page -->
<div id="uploading-div">
    <img src="<?php  echo get_template_directory_uri(); ?>/images/uploading.gif">

</div>
<?php wp_footer(); ?>
</body>
</html>