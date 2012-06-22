<?php
/*
Plugin Name: WPenlighten
Plugin URI: https://github.com/funkjedi/WPenlighten
Description: Wordpress optimizations and useful template tags and shortcodes
Version: 0.1.1
Author: Tim Robertson
Author URI: http://funkjedi.com/
License: MIT
*/

define('WP_GITHUB_FORCE_UPDATE', true);

require dirname(__FILE__) . '/cleanup.php';
require dirname(__FILE__) . '/template-tags.php';
require dirname(__FILE__) . '/shortcodes.php';
require dirname(__FILE__) . '/widgets.php';
require dirname(__FILE__) . '/vendor/bootstrap.php';
require dirname(__FILE__) . '/vendor/rawr.php';


add_action('init', 'wpenlighten_github_updater');
function wpenlighten_github_updater() {
	if (is_admin()) {
		require_once dirname(__FILE__) . '/vendor/wordpress-github-plugin-updater.php';
		$config = array(
			'slug'               => plugin_basename(__FILE__),
			'proper_folder_name' => 'wpenlighten',
			'api_url'            => 'https://api.github.com/repos/funkjedi/WPenlighten',
			'raw_url'            => 'https://raw.github.com/funkjedi/WPenlighten/master',
			'github_url'         => 'https://github.com/funkjedi/WPenlighten',
			'zip_url'            => 'https://github.com/funkjedi/WPenlighten/zipball/master',
			'sslverify'          => true,
			'requires'           => '3.0',
			'tested'             => '3.4',
			'readme'             => 'READMEN.md'
		);
		new WPGitHubUpdater($config);
	}
}
