<?php


function add_podcast_SM(  )
{
   // global $wp_rewrite;

    add_feed('podcast','do_sm_feed_podcast');

}


function do_sm_feed_podcast()
{
    global $wp_query, $wpdb;
    $wp_query->get_posts();
    load_template( SOINUMAPA_LIB . 'podcast-feed.php');
}

?>
