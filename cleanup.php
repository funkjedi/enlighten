<?php

add_theme_support('nice-search');

require dirname(__FILE__) . '/vendor/roots/utils.php';
require dirname(__FILE__) . '/vendor/roots/cleanup.php';
require dirname(__FILE__) . '/vendor/roots/nav.php';


// Set the post revisions to 5 unless previously set to avoid DB bloat
if (!defined('WP_POST_REVISIONS')) { define('WP_POST_REVISIONS', 5); }

// http://www.deluxeblogtips.com/2011/01/remove-dashboard-widgets-in-wordpress.html
add_action('admin_init', 'wpcleanup_remove_dashboard_widgets');
function wpcleanup_remove_dashboard_widgets() {
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	remove_meta_box('dashboard_primary', 'dashboard', 'normal');
	remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
	remove_meta_box('dashboard_quick_press', 'dashboard', 'normal');
	remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
	remove_meta_box('dashboard_recent_drafts', 'dashboard', 'normal');
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}


add_filter('the_posts', 'enlighten_search_wp_query', 8);
function enlighten_search_wp_query($args) {
	global $wp_query;
	if (isset($wp_query->query_vars['s'])) {
		$wp_query->query_vars['s'] = urldecode($wp_query->query_vars['s']);
	}
	return $args;
}

add_filter('page_css_class', 'enlighten_page_css_class', 10, 5);
function enlighten_page_css_class($css_class, $page, $depth, $args, $current_page) {
	$css_class = array("page-$page->post_name");
	if ($page->ID === $current_page) {
		$css_class[] = 'active';
	}
	return $css_class;
}


// Add the ability to use category specific templates
add_filter('single_template', 'enlighten_single_template');
function enlighten_single_template($template) {
	foreach(get_the_category() as $category) {
		if ($tpl = locate_template("single-$category->slug.php")) return $tpl;
		if ($tpl = locate_template("single-$category->term_id.php")) return $tpl;
	}
	return $template;
}

// Add the ability to use templates when display Post 2 Post widget or shortcodes
add_filter('p2p_widget_html', 'enlighten_p2p_template_handling', 10, 4);
add_filter('p2p_shortcode_html', 'enlighten_p2p_template_handling', 10, 4);
function enlighten_p2p_template_handling($html, $connected, $ctype, $mode) {
	$direction = $ctype->get_direction();
	if (locate_template("p2p-$ctype->name.php") or locate_template("p2p-$ctype->name-$direction.php")) {
		ob_start();
		enlighten_loop($connected->items, false);
		get_template_part("p2p-$ctype->name", $direction);
		return ob_get_clean();
	}
	return $html;
}


// Helper function for providing "first" and "last" classes to the menu walker
function enlighten_get_menu_order($item, $args, $which = 'first') {
	static $cache = array('first' => array(), 'last' => array());

	// return cached item
	if (isset($cache[$which][$item->menu_item_parent]))
		return $cache[$which][$item->menu_item_parent];

	// Get the menu order for both the menu level and direction
	if (!isset($cache[$which][$item->menu_item_parent]) && $args->theme_location && ($locations = get_nav_menu_locations()) && isset($locations[$args->theme_location])) {
		global $wpdb;
		$found = $wpdb->get_var($wpdb->prepare("
			SELECT p.menu_order FROM {$wpdb->prefix}term_relationships tr
			INNER JOIN {$wpdb->prefix}posts p ON p.ID = tr.object_id
			INNER JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID AND pm.meta_key = '_menu_item_menu_item_parent' AND pm.meta_value = %s
			WHERE tr.term_taxonomy_id = %d
			ORDER BY p.menu_order ". ($which === 'last' ? 'DESC' : 'ASC') ." LIMIT 1", $item->menu_item_parent, $locations[$args->theme_location]));
		if ($found) {
			return $cache[$which][$item->menu_item_parent] = $found;
		}
	}
}
