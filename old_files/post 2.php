<?php
header('Content-type: text/html');
require( '../../../wp-load.php' );

if (isset($_GET["postid"])) {
	$id = $_GET['postid'];
}else{
	die();
}

$post = get_post($id);
setup_postdata($post);
if (is_null($post)){
	die();
}
$id = $post->ID;
$title = get_the_title($id);
$link = get_permalink($id);
$categ=get_the_terms($id,"tree");
$content = get_the_content("", "", "");
$content = apply_filters('the_content', $content);
$content = str_replace(']]>', ']]&gt;', $content);
$thumb="";
if (has_post_thumbnail($id)){	
	$thumbid = get_post_thumbnail_id( $id );
	$size = apply_filters( 'post_thumbnail_size', 'thumbnail' );
	$thumb = wp_get_attachment_image( $thumbid, $size, false, '' );
	$imagen_att=wp_get_attachment_url($thumbid);
}

$enarbolador_id=get_post_meta($id,"_enarbolador",true);
$enarbolado=get_the_title($enarbolador_id);
$enarbolado_link=get_page_link($enarbolador_id);
?>

<div class="hentry afs-fontin-sans marker-info" id="post-<?php print $id; ?>">
	<div class="tree-title">
		<h2><a href="<?php print $link; ?>"><?php print $title; ?></a></h2>	
		<i><?php print __("Enarbolado por: "); ?> <a href="<?php print $enarbolado_link; ?>" ><?php print $enarbolado; ?></a></i>
	</div>
	<div class="tree-thumb">
		<a href="<?php print $imagen_att; ?>" rel="post-images"><?php print $thumb; ?></a>
	</div>
	<div class="tree-entry">
		<strong><?php print _("Especie: ")?></strong><a href="<?php print get_term_link($categ[0],"tree"); ?>"><?php print $categ[0]->name; ?></a><br />
		<?php print $content; ?>
	</div>	
</div>
