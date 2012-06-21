<?php


function wp_enqueue_less($stylesheet) {
	if (!class_exists('lessc')) {
		require dirname(__FILE__) . '/vendor/lessc.php';
	}
	if (class_exists('lessc')) {
		try {
			$in = locate_template("/$stylesheet");
			$out = locate_template("/$stylesheet.css");

			// compile file $in to file $out if $in is newer than $out
			// returns true when it compiles, false otherwise
			if (!is_file($out) || filemtime($in) > filemtime($out)) {
				$less = new lessc($in);
				file_put_contents($out, $less->parse());
			}

			wp_enqueue_style('lessc' . time(), get_template_directory_uri() . "/$stylesheet.css");
		}
		catch (Exception $e) {}
	}
}


function the_content_from($page_id, $filter = true) {
	if (is_numeric($page_id)) {
		$post = get_page($page_id);
	}
	else {
		$post = get_page_by_title($page_id);
	}
	if ($post) {
		echo $filter
			? apply_filters("the_content", $post->post_content)
			: $post->post_content;
	}
}

function get_template_part_for($args, $slug, $name = "") {
	if (is_array($args)) {
		foreach (get_posts($args) as $post) {
			setup_postdata($GLOBALS['post'] = $post);
			get_template_part($slug, $name);
			do_action('get_template_part_using', $post->ID);
		}
	}
	else {
		$post = is_numeric($args)
			? get_page($args)
			: get_page_by_title($args);
		setup_postdata($GLOBALS['post'] = $post);
		get_template_part($slug, $name);
		do_action('get_template_part_using', $post->ID);
	}
	// instead of using wp_reset_postdata() we reinstate the original $post;
	setup_postdata($GLOBALS['post'] = $original_post);
}


function the_loops($loopID, $args = null, $template = null) {
	if (function_exists('tl_get_loop_parameters')) {
		if (isset($args['template'])) {
			$template = $args['template']; unset($args['template']);
		}
		else {
			$loop = tl_get_loop_parameters($loopID);
			$template = $loop['template'];
		}
		echo tl_display_loop($loopID, $template, $args, 'shortcode');
	}
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
	if ($image = wp_get_attachment_image_src(has_post_thumbnail_for($multi_post_thumbnail), $size)) {
		echo $background_image ? "background-image: url({$image[0]});" : $image[0];
	}
}


function youtube_id($url) {
	$matches = array();
	preg_match('#http://(www.youtube|youtube|[A-Za-z]{2}.youtube)\.com/(watch\?v=|w/\?v=|\?v=)([\w-]+)(.*?)#i', $url, $matches);
	if (isset($matches[3]) and !empty($matches[3])) {
		return $matches[3];
	}
}
