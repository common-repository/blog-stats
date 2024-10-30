<?php
/*
Plugin Name: Blog Stats
Plugin URI: http://www.improvingtheweb.com/wordpress-plugins/blog-stats/
Description: Adds shortcodes for important blog stats, updates daily automatically.
Author: Improving The Web
Version: 1.0
Author URI: http://www.improvingtheweb.com/
*/

if (is_admin()) {
	register_activation_hook(__FILE__, 'bs_install');
	register_deactivation_hook(__FILE__, 'bs_uninstall');
	
	add_action('admin_menu', 'bs_admin_menu');
}

add_shortcode('user_count', 'bs_user_count');
add_shortcode('post_count', 'bs_post_count');
add_shortcode('page_count', 'bs_page_count');
add_shortcode('comment_count', 'bs_comment_count');
add_shortcode('trackback_count', 'bs_trackback_count');
add_shortcode('avg_comments_per_post', 'bs_avg_comments_per_post');
add_shortcode('category_count', 'bs_category_count');
add_shortcode('tag_count', 'bs_tag_count');
add_shortcode('link_count', 'bs_link_count');
add_shortcode('pagerank', 'bs_pagerank');
add_shortcode('technorati_authority', 'bs_technorati_authority');
add_shortcode('technorati_rank', 'bs_technorati_rank');
add_shortcode('alexa_rank', 'bs_alexa_rank');
add_shortcode('feedburner_subscribers', 'bs_feedburner_subscribers');
add_shortcode('google_backlinks', 'bs_google_backlinks');
add_shortcode('yahoo_backlinks', 'bs_yahoo_backlinks');
add_shortcode('delicious_bookmarks', 'bs_delicious_bookmarks');

function bs_install() {
	global $wpdb, $bs_options;
			
	if (!get_option('bs_options')) {
		add_option('bs_options', array('feedburner_id' => '', 'feedburner_location' => 'old', 'after_install' => 1));
	}	

	wp_schedule_event(time() + 3600, 'daily', 'bs_calculate_daily_stats');
}

function bs_uninstall() {
	delete_option('bs_options');
	wp_clear_scheduled_hook('bs_calculate_daily_stats');
}

function bs_calculate_daily_stats() {
	require dirname(__FILE__) . '/update.php'; 
		
	$stats_updater = new BlogStatsUpdater();
		
	update_option('bs_options', $stats_updater->execute());
}

function bs_stat($what, $formatted=true) {
	global $bs_options;
	if (empty($bs_options)) {
		$bs_options = get_option('bs_options');
	}
	
	if ($formatted) {
		return number_format($bs_options[$what]);
	} else {
		return $bs_options[$what];
	}
}

function bs_user_count() {
	return bs_stat('user_count');
}

function bs_post_count() {
	return bs_stat('post_count');
}

function bs_page_count() {
	return bs_stat('page_count');
}

function bs_comment_count() {
	return bs_stat('comment_count');
}

function bs_trackback_count() {
	return bs_stat('trackback_count');
}

function bs_avg_comments_per_post() {
	return bs_stat('avg_comments_per_post');
}

function bs_category_count() {
	return bs_stat('category_count');
}

function bs_tag_count() {
	return bs_stat('tag_count');
}

function bs_link_count() {
	return bs_stat('link_count');
}

function bs_pagerank() {
	return bs_stat('pagerank');
}

function bs_technorati_authority() {
	return bs_stat('technorati_authority');
}

function bs_technorati_rank() {
	return bs_stat('technorati_rank');
}

function bs_alexa_rank() {
	return bs_stat('alexa_rank');
}

function bs_feedburner_subscribers() {
	return bs_stat('feedburner_subscribers');
}

function bs_google_backlinks() {
	return bs_stat('google_backlinks');
}

function bs_yahoo_backlinks() {
	return bs_stat('yahoo_backlinks');
}

function bs_delicious_bookmarks() {
	return bs_stat('delicious_bookmarks');
}

function bs_admin_menu() {
	add_submenu_page('options-general.php', 'Blog Stats', 'Blog Stats', 8, 'Blog Stats', 'bs_submenu');
}

function bs_submenu() {
	require dirname(__FILE__) . '/admin.php'; 
}
?>