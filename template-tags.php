<?php

function __d() {
    print "<pre>";
    foreach(func_get_args() as $index => $arg) {
        print_r($arg);
        echo "\n";
    }
    exit;
}

function mdetect($method) {
	static $mdetect;
	if (!$mdetect) {
		require_once dirname(__FILE__) . '/vendor/mdetect.php';
		$mdetect = new uagent_info();
	}
	return $mdetect->$method();
}


function sub_field_index() {
	global $acf_field;
	return $acf_field[count($acf_field) - 1]['row'];
}

function the_sub_field_index() {
	echo sub_field_index();
}


function get_theme_url($path) {
	return get_template_directory_uri() . '/' . ltrim($path, '/');
}

function the_theme_url($path) {
	print get_theme_url($path);
}


function add_post_thumbnail($name, $id, $post_types = array('page', 'post')) {
	if (class_exists('MultiPostThumbnails')) {
		foreach ($post_types as $post_type)
			new MultiPostThumbnails(array('label' => $name, 'id' => $id, 'post_type' => $post_type));
	}
}

function has_post_thumbnail_src($multi_post_thumbnail = '') {
	global $post;
	$attachmentID = null;
	if (class_exists('MultiPostThumbnails')) {
		if ($multi_post_thumbnail) {
			$attachmentID = MultiPostThumbnails::get_post_thumbnail_id(get_post_type($post), $multi_post_thumbnail, $post->ID);
		}
		elseif (function_exists('qtrans_getLanguage')) {
			$attachmentID = MultiPostThumbnails::get_post_thumbnail_id(get_post_type($post), 'featured-image-' . qtrans_getLanguage(), $post->ID);
		}
	}
	if (!isset($attachmentID) or !$attachmentID) {
		$attachmentID = get_post_thumbnail_id($post->ID);
	}
	return $attachmentID;
}

function the_post_thumbnail_src($size = 'full', $background_image = false, $multi_post_thumbnail = '') {
	if ($image = wp_get_attachment_image_src(has_post_thumbnail_src($multi_post_thumbnail), $size)) {
		echo $background_image ? "background-image: url({$image[0]});" : $image[0];
	}
}


function the_content_from($page_id, $suppress_filters = false) {
	if (is_numeric($page_id)) {
		$post = get_page($page_id);
	}
	else {
		$post = get_page_by_title($page_id);
	}
	if ($post) {
		echo $suppress_filters
			? $post->post_content
			: apply_filters("the_content", $post->post_content);
	}
}

function get_template_part_for($slug, $args) {
	$name = "";
	if (func_num_args() > 2) {
		list($slug, $name, $args) = func_get_args();
	}
	display_posts($args, 'get_template_part', array($slug, $name));
}

function display_posts($args, $callback, array $callback_args = array()) {
	$original_post = $GLOBALS['post'];
	if (is_array($args)) {
		foreach (get_posts($args) as $post) {
			setup_postdata($GLOBALS['post'] = $post);
			call_user_func_array($callback, $callback_args);
			do_action('display_posts', $post->ID);
		}
	}
	else {
		$post = is_numeric($args) ? get_page($args) : get_page_by_title($args);
		setup_postdata($GLOBALS['post'] = $post);
		call_user_func_array($callback, $callback_args);
		do_action('display_posts', $post->ID);
	}
	// instead of using wp_reset_postdata() we reinstate the original $post;
	setup_postdata($GLOBALS['post'] = $original_post);
}


class Faux_Loop {
	function __construct($posts, $max_num_pages = 1) {
		$this->posts = $posts;
		$this->post_count = count($posts);
		$this->current_post = -1;
		$this->original_post = $GLOBALS['post'];
		$this->max_num_pages = $max_num_pages;
	}
	function have_posts() {
		if ($this->current_post + 1 < $this->post_count) {
			return true;
		}
		$this->reset();
		return false;
	}
	function the_post() {
		$this->current_post += 1;
		setup_postdata($GLOBALS['post'] = $this->posts[$this->current_post]);
	}
	function reset() {
		$this->current_post = -1;
		setup_postdata($GLOBALS['post'] = $this->original_post);
	}
}

function the_loop($args = null, $query = 'get_posts') {
	static $loop;
	if (isset($args)) {
		if ($loop) {
			$loop->reset();
		}
		switch ($query) {
			case 'get_posts':
				$loop = new Faux_Loop(get_posts($args));
				break;
			case 'wp_query':
				$args = wp_parse_args($args, array(
					'post_status'         => 'publish',
					'posts_per_page'      => 5,
					'offset'              => 0,
					'cat'                 => 0,
					'orderby'             => 'post_date',
					'order'               => 'DESC',
					'include'             => array(),
					'exclude'             => array(),
					'meta_key'            => '',
					'meta_value'          => '',
					'post_type'           => 'post',
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'suppress_filters'    => true
				));
				$wpq = new WP_Query($args);
				$loop = new Faux_Loop($wpq->posts, $wpq->max_num_pages);
				break;
			default:
				if (is_object($args) and get_class($args) === 'WP_Query') {
					$loop = new Faux_Loop($args->posts, $args->max_num_pages);
					break;
				}
				$loop = new Faux_Loop($args);
		}
	}
	if (isset($loop) === false) {
		$loop = new Faux_Loop(array($GLOBALS['post']));
	}
	return $loop;
}



function youtube_id($url) {
	$matches = array();
	preg_match('#(?<=(?:v|i)=)[a-zA-Z0-9-]+(?=&)|(?<=(?:v|i)\/)[^&\n]+|(?<=embed\/)[^"&\n]+|(?<=(?:v|i)=)[^&\n]+|(?<=youtu.be\/)[^&\n]+#i', $url, $matches);
	if (isset($matches[0]) and !empty($matches[0])) {
		return trim($matches[0]);
	}
}

function vimeo_id($url) {
	$matches = array();
	preg_match( '#https?://(www.vimeo|vimeo)\.com(/|/clip:)(\d+)(.*?)#i', $url, $matches);
	if (isset($matches[3]) and !empty($matches[3])) {
		return $matches[3];
	}
}
