<?php
/*
Plugin Name: Enlighten
Plugin URI: https://github.com/funkjedi/enlighten
Description: Wordpress optimizations and useful template tags and shortcodes
Version: 0.1.3
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



add_filter('style_loader_src', 'enlighten_style_loader_src', 10, 2);
function enlighten_style_loader_src($src, $handle) {
	global $wp_styles;

	// quick check for scss stylesheets
	if (strpos($src, '.scss') === false) {
		return $src;
	}

	// wp_enqueue_style automatically appends base_url the src
	$in = preg_replace("|^{$wp_styles->base_url}|i", "", $src);

	// remaining http/https urls are external and should be untouched
	if (preg_match('|^(https?:)?//|', $in)) {
		return $src;
	}

	$parts = parse_url($in);
	$paths = pathinfo($parts['path']);

	// allow scss stylesheets should be compiled
	if ($paths['extension'] !== 'scss') {
		return $src;
	}

	$upload_dir = wp_upload_dir();
	$in = $parts['path'];
	$out = $upload_dir['basedir'] . '/' . $paths['filename'] . '.css';

	// construct a complete path
	if (strpos($in, '/') === 0) {
		$in = $_SERVER['DOCUMENT_ROOT'] . $in;
	}
	else {
		$in = get_stylesheet_directory() . '/' . $in;
	}

	// setup scssc parser
	require_once dirname(__FILE__) . '/vendor/scssphp/scss.inc.php';
	$parser = new scssc();
	$parser->setImportPaths(dirname($in));
	$parser->registerFunction('theme_url', 'enlighten_scss_function_theme_url');
	$parser->registerFunction('get_option', 'enlighten_scss_function_get_option');

	if (!is_file($out) || filemtime($in) > filemtime($out)) {
		try {
			$data = file_get_contents($in);
			file_put_contents($out, $parser->compile($data));
		}
		catch (Exception $e) {
			enlighten_log($e->getMessage());
			return $src;
		}
	}

	return $upload_dir['baseurl'] . '/' . $paths['filename'] . '.css?' . $parts['query'];
}

function enlighten_scss_function_theme_url($value) {
	return 'url(' . get_theme_url($value[0][2][0][2][0]) . ')';
}

function enlighten_scss_function_get_option($value) {
	$option = get_option($value[0][2][0], "");

	// cast color values to scss colors
	if (preg_match('/^\s*(#([0-9a-f]{6})|#([0-9a-f]{3}))\s*$/Ais', $option, $matches)) {
		$color = array('color');
		if (isset($matches[3])) {
			$num = $matches[3];
			$width = 16;
		} else {
			$num = $matches[2];
			$width = 256;
		}
		$num = hexdec($num);
		foreach (array(3,2,1) as $i) {
			$t = $num % $width;
			$num /= $width;
			$color[$i] = $t * (256/$width) + $t * floor(16/$width);
		}
		$option = $color;
	}

	return $option;
}
