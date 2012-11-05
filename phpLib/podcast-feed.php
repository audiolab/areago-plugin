<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
$more = 1;
echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';
?>

<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">

	<channel>
		<title><?php bloginfo_rss('name');
wp_title_rss(); ?></title>
		<description><?php bloginfo_rss("description") ?></description>
		<link><?php bloginfo_rss('url') ?></link>
		<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
		<generator>Soinumapa Plugin for Wordpress</generator>
		<language><?php echo get_option('rss_language'); ?></language>
		<itunes:category text="Arts" />
		<itunes:subtitle>Podcast for <?php bloginfo_rss('name'); ?></itunes:subtitle>
		<itunes:summary>Grabaciones de SOINUMAPA.NET</itunes:summary>
		<itunes:author>Soinumapa Plugin</itunes:author>
		<itunes:owner>
			<itunes:name>Soinumapa.net</itunes:name>
			<itunes:email>info@soinumapa.net</itunes:email>
		</itunes:owner>
		<itunes:image href="http://www.soinumapa.net/soinu.jpg" />
		<?php
		do_action('rss2_head');

		//global $wpdb;
		global $SoinuMapa;
		//$q='SELECT * FROM wp_xspf_player';
		//$sonidos=$wpdb->get_results($q,"ARRAY_A");
		?>
		<?php
		while (have_posts ()) : the_post();
			$id = get_the_ID();
			$content = get_the_content();
			$filen = $SoinuMapa->attDataFile($id);

			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);
			$itunes_summary = limitStringLength(flatTextEncode(strip_tags($content)), 4000);
		?>

			<item>
	                        <title><?php the_title_rss(); ?></title>
	                        <itunes:author><?php the_author() ?></itunes:author>
	                        <link><?php the_permalink_rss() ?></link>
	                        <guid isPermaLink="false"><?php the_guid(); ?></guid>
	                        <itunes:summary><?php echo $itunes_summary; ?></itunes:summary>
	                        <enclosure url="<?php echo $filen; ?>" type="audio/mpeg" length="<?php echo filesize($filen); ?>"/>

	                        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
	                        <itunes:keywords><?php
			$posttags = get_the_tags();
			if ($posttags) {
				foreach ($posttags as $tag) {
					echo $tag->name . ', ';
				}
			}
		?></itunes:keywords>

<?php do_action('rss2_item'); ?>

		</item>

		<?php endwhile; ?>
		</channel>
	</rss>

<?php

			function limitStringLength($string, $limit) {
				if (strlen($string) > $limit)
					$string = substr($string, 0, strrpos(substr($string, 0, $limit - 6), ' ')) . ' [...]';

				return $string;
			}

			function flatTextEncode($value) {
				if (DB_CHARSET != 'utf8') // Check if the string is UTF-8
					$value = utf8_encode($value); // If it is not, convert to UTF-8 then decode it...
					// Code added to solve issue with KimiliFlashEmbed plugin and also remove the shortcode for the WP Audio Player
					// 99.9% of the time this code will not be necessary
				$value = preg_replace("/\[(kml_(flash|swf)embed|audio\:)\b(.*?)(?:(\/))?(\]|$)/isu", '', $value);

				if (version_compare("5", phpversion(), ">"))
					$value = preg_replace('/&nbsp;/ui', ' ', $value); // Best we can do for PHP4
 else
					$value = @html_entity_decode($value, ENT_COMPAT, 'UTF-8'); // Remove any additional entities such as &nbsp;
 $value = preg_replace('/&amp;/ui', '&', $value); // Best we can do for PHP4. precaution in case it didn't get removed from function above.

				return wp_specialchars($value);
			}
?>
