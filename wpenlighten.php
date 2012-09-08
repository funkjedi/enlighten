<?php
/*
Plugin Name: WPenlighten
Plugin URI: https://github.com/funkjedi/WPenlighten
Description: Wordpress optimizations and useful template tags and shortcodes
Version: 0.1.2
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


//add_action('init', 'wpenlighten_github_updater');
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
			'readme'             => 'README.md'
		);
		new WPGitHubUpdater($config);
	}
}


add_filter('style_loader_src', 'wp_enqueue_style_less', 10, 2);
function wp_enqueue_style_less($src, $handle) {
	if (stripos($src, '.less') === false) {
		return $src;
	}
	$path = pathinfo(parse_url($src, PHP_URL_PATH));
	if ($path['extension'] === 'less') {
		$upload_dir = wp_upload_dir();

		// build file paths for stylesheets
		$in = "$_SERVER[DOCUMENT_ROOT]$path[dirname]/$path[basename]"; $filename = substr(sha1($in), -8) . "_$path[filename].css";
		$out = "$upload_dir[basedir]/$filename";

		// compile file $in to file $out if $in is newer than $out
		// returns true when it compiles, false otherwise
		if (!is_file($out) || filemtime($in) > filemtime($out)) {
			try {
				require_once dirname(__FILE__) . '/vendor/lessc.php';
				$less = new lessc($in);
				file_put_contents($out, $less->parse());
			}
			catch (Exception $e) {
				print '<!-- ' . $e->getMessage() . ' -->';
			}
		}

		return $upload_dir['baseurl'] . '/' . $filename;
	}

	return $src;
}
