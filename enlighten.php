<?php
/*
Plugin Name: Enlighten
Plugin URI: https://github.com/funkjedi/enlighten
Description: Wordpress optimizations and useful template tags and shortcodes
Version: 0.1.2
Author: Tim Robertson
Author URI: http://funkjedi.com/
License: MIT
*/

require dirname(__FILE__) . '/cleanup.php';
require dirname(__FILE__) . '/template-tags.php';
require dirname(__FILE__) . '/shortcodes.php';
require dirname(__FILE__) . '/widgets.php';
require dirname(__FILE__) . '/vendor/bootstrap.php';
require dirname(__FILE__) . '/vendor/rawr.php';
require dirname(__FILE__) . '/vendor/wpformhelper.php';


// allow Advanced Custom Fields and Custom Post Type Switcher to work together
add_filter('pts_post_type_filter', 'enlighten_pts_disable');
function enlighten_pts_disable($args) {
	if (get_post_type() === 'acf') {
		$args = array('name' => 'acf');
	}
	return $args;
}

// run all Advanced Custom Fields through qTranslate
add_filter('acf_load_value', 'enlighten_acf_load_value');
function enlighten_acf_load_value($value) {
	return is_string($value) ? __($value) : $value;
}


add_filter('style_loader_src', 'wp_enqueue_style_libraries', 10, 2);
function wp_enqueue_style_libraries($src, $handle) {
	if (stripos($src, '.css') === strlen($src) - 4) {
		return $src;
	}
	$path = pathinfo(parse_url($src, PHP_URL_PATH));
	if (in_array( $path['extension'], array('less','sass','scss') )) {
		$upload_dir = wp_upload_dir();

		// build file paths for stylesheets
		$in = "$_SERVER[DOCUMENT_ROOT]$path[dirname]/$path[basename]"; $filename = "$path[filename].$path[extension]." . substr(sha1($in), -8) . ".css";
		$out = "$upload_dir[basedir]/$filename";

		switch ($path['extension']) {

			// compile less files
			case 'less':
				try {
					require_once dirname(__FILE__) . '/vendor/lessc.php';
					$less = new lessc();
					$less->checkedCompile($in, $out);
				}
				catch (Exception $e) {
					print '<!-- ' . $e->getMessage() . ' -->';
					return $src;
				}
				break;


			// compile scss files
			case 'scss':
				try {
					require_once dirname(__FILE__) . '/vendor/scssphp/scss.inc.php';
					$parser = new scssc();
					$parser->setImportPaths("$_SERVER[DOCUMENT_ROOT]$path[dirname]");
					$parser->registerFunction("asset-url", create_function('$a', 'return "url(" . get_template_directory_uri() . "/" . $a[0][2][0][2][0] . ")";'));

					if (!is_file($out) || filemtime($in) > filemtime($out)) {
						$data = file_get_contents($in);
						file_put_contents($out, $parser->compile($data));
					}
				}
				catch (Exception $e) {
					print '<!-- ' . $e->getMessage() . ' -->';
					return $src;
				}
				break;

		}

		return $upload_dir['baseurl'] . '/' . $filename . '?' . time();
	}

	return $src;
}
