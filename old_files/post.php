<?php
header('Content-type: text/html');
require( '../../../wp-load.php' );



if (isset($_GET["postid"])) {
	$id = $_GET['postid'];
}

$prefix = "wp_";
$t = "";
$post = get_post($id);
global $SoinuMapa;

setup_postdata($post);
$id = get_the_ID();
the_marker($id);
$category_for_id = get_the_category($id);
$category_id = $category_for_id[0]->term_id;
$title = get_the_title($id);
$link = get_permalink($id);
$tags = tags($id);
$categ = categories($category_for_id);
$content = get_the_content("", "", "");
$content = apply_filters('the_content', $content);
$content = str_replace(']]>', ']]&gt;', $content);
$archivo=$SoinuMapa->marker['data']['file'];


$t = "<div class=\"hentry\" id=\"post-$id\">
                    <h2>$title</h2>
                    <div class=\"entry\">
                    $content
			";
?>

<?php
echo $t;
?>

	<div id="jquery_jplayer"></div>

	<div class="jp-single-player">
		<div class="jp-interface">
			<ul class="jp-controls">
				<li><button href="#" id="jplayer_play" class="jp-play" tabindex="1">play</button></li>
				<li><button href="#" id="jplayer_pause" class="jp-pause" tabindex="1">pause</button></li>
				<li><button href="#" id="jplayer_stop" class="jp-stop" tabindex="1">stop</button></li>
				<li class="jplayer_buffer">buffering</li>
			</ul>
			<a href="<?php echo $archivo ?>" id="sound_download" tabindex="1" target="_blank">Download</a>
		</div>
	</div>

	<?
	$t = "            </div>
                    $tags
                    $categ
                </div>
                    ";
	echo $t;

	unset($SoinuMapa);
	?>
