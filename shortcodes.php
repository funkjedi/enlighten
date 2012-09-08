<?php

add_shortcode('content_from','shortcode__content_from');
function shortcode__content_from($atts) {
	extract(shortcode_atts(array('page' => 0), $atts));
	ob_start();
	the_content_from($page);
	return ob_get_clean();
}

add_shortcode('load_template','shortcode__load_template');
function shortcode__load_template($atts) {
	extract(shortcode_atts(array('name' => ''), $atts));
	ob_start();
	get_template_part($name);
	return ob_get_clean();
}

add_shortcode('display_posts','shortcode__display_posts');
function shortcode__display_posts($atts) {
	extract(shortcode_atts(array('where' => '', 'using' => ''), $atts));
	ob_start();
	$where = strtr(html_entity_decode($where), '|', '&');
	display_posts(wp_parse_args($where), 'get_template_part', array($using));
	return ob_get_clean();
}

add_shortcode('loop','shortcode__loop');
function shortcode__loop($atts) {
	extract(shortcode_atts(array('where' => '', 'tpl' => ''), $atts));
	ob_start();
	$where = strtr(html_entity_decode($where), '|', '&');
	the_loop(wp_parse_args($where));
	get_template_part($tpl);
	return ob_get_clean();
}
