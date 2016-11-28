<?php

function get_query_loop($args = array()){
	return \Enlighten\Loop::create($args);
}

function get_theme_dir($path){
	return get_stylesheet_directory() . '/' . ltrim($path, '/');
}

function get_theme_url($path){
	return trans(get_stylesheet_directory_uri() . '/' . ltrim($path, '/'));
}

function the_theme_url($path){
	print get_theme_url($path);
}

function get_versioned_theme_url($path){
	$timestamp = @filemtime(get_template_directory() . '/' . ltrim($path, '/'));
	return get_theme_url("{$path}?t=".$timestamp);
}

function the_versioned_theme_url($path){
	print get_versioned_theme_url($path);
}

function json_attr($data){
	return htmlentities(wp_json_encode($data), ENT_QUOTES, 'UTF-8');
}

function the_language_switcher($format = '<a class="languageswitcher" href="{url}" hreflang="{code}" title="{name}"><span>{name}</span></a>'){
	global $q_config;
	if (function_exists('qtranxf_getSortedLanguages')) {
		foreach(qtranxf_getSortedLanguages() as $language) {
			if ($language !== $q_config['language']) {
				print strtr($format, array(
					'{url}'  => qtranxf_convertURL('', $language, false, true),
					'{code}' => $language,
					'{name}' => $q_config['language_name'][$language],
				));
				break;
			}
		}
	}
}

function trans($text = '', $alt = ''){
	$locale = 'en';
	if (function_exists('qtranxf_getLanguage')) {
		$locale = qtranxf_getLanguage();
	}
	if (empty($text)) {
		return $locale;
	}
	if (empty($alt) === false) {
		$text = [$text, $alt];
	}
	if (is_array($text)) {
		if (function_exists('qtranxf_getLanguage')) {
			$text = '[:en]' . $text[0] . '[:fr]' . $text[1];
		} else {
			$text = $text[0];
		}
	}
	return strtr(__($text), apply_filters('trans_token', array('{locale}' => $locale), $locale));
}

if (!function_exists('t')):
	function t($text = '', $alt = ''){
		return trans($text, $alt);
	}
endif;

function str_trim_words($text, $length = 140, $more = '&hellip;'){
	$text = wp_strip_all_tags($text);
	if (strlen($text) > $length) {
		$text = substr($text, 0, $length);
		$words = preg_split('/[\n\r\t ]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
		array_pop($words);
		$text = implode(' ', $words) . $more;
	}
	return $text;
}

function get_template_with($__path, array $__data = array()){
	extract($__data, EXTR_SKIP);
	$__path = locate_template("{$__path}.php");
	if ($__path) {
		include $__path;
	}
}

function category_classes($classes, $taxonomy = 'category'){
	global $post;
	$classes = (array)$classes;
	if ($post) {
		$terms = array_merge((array)get_the_terms($post->ID, $taxonomy), (array)get_the_tags($post->ID));
		$terms = array_filter($terms);
		foreach ($terms as $term) {
			$classes[] = "term-{$term->term_id}";
		}
	}
	print implode(' ', $classes);
}

function get_the_category_object($taxonomy = 'category'){
	global $post;
	if ($post) {
		$terms = get_the_terms($post->ID, $taxonomy);
		if (is_array($terms) && count($terms) > 0) {
			$term = current($terms);
			$term->colour = get_field('colour', $term);
			return $term;
		}
	}
}

function get_the_category_name($taxonomy = 'category'){
	$term = get_the_category_object($taxonomy);
	if ($term) {
		return $term->name;
	}
}

function the_category_name($taxonomy = 'category'){
	print get_the_category_name($taxonomy);
}

function get_attachment_image($postID, $size = 'full', $background_image = false){
	$image = wp_get_attachment_image_src($postID, $size);
	if ($image) {
		if ($background_image) {
			return 'background-image: url(' . $image[0] .');';
		}
		return $image[0];
	}
}

function get_featured_image_alt(){
	$attachment = get_post(get_post_thumbnail_id(get_the_ID()));
	return trim(get_post_meta($attachment->ID, '_wp_attachment_image_alt', true));
}

function the_featured_image_alt(){
	print get_featured_image_alt();
}

function get_featured_image_caption(){
	$attachment = get_post(get_post_thumbnail_id(get_the_ID()));
	return trim($attachment->post_excerpt);
}

function get_featured_image($size = 'full', $background_image = false){
	$attachmentID = get_post_thumbnail_id(get_the_ID());
	return get_attachment_image($attachmentID, $size, $background_image);
}

function the_featured_image($size = 'full', $background_image = false){
	print get_featured_image($size, $background_image);
}

function get_image_field($key, $size = 'full', $background_image = false){
	return get_attachment_image(get_field($key), $size, $background_image);
}

function the_image_field($key, $size = 'full', $background_image = false){
	print get_image_field($key, $size, $background_image);
}

function get_sub_image_field($key, $size = 'full', $background_image = false){
	return get_attachment_image(get_sub_field($key), $size, $background_image);
}

function the_sub_image_field($key, $size = 'full', $background_image = false){
	print get_sub_image_field($key, $size, $background_image);
}

function get_sub_image_field_alt($key){
	return trim(get_post_meta(get_sub_field($key), '_wp_attachment_image_alt', true));
}

function the_sub_image_field_alt($key){
	print get_sub_image_field_alt($key);
}
