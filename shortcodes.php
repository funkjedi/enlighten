<?php

add_shortcode('content_from','enlighten_shortcode__content_from');
function enlighten_shortcode__content_from($atts) {
	extract(shortcode_atts(array('page' => 0), $atts));
	ob_start();
	the_content_from($page);
	return ob_get_clean();
}

add_shortcode('enlighten_loop','enlighten_shortcode__enlighten_loop');
function enlighten_shortcode__enlighten_loop($atts) {
	extract(shortcode_atts(array('where' => '', 'file' => ''), $atts));
	ob_start();
	$where = strtr(html_entity_decode($where), '|', '&');
	enlighten_loop(wp_parse_args($where));
	get_template_part($tpl);
	return ob_get_clean();
}

add_shortcode('use_template','enlighten_shortcode__load_template');
function enlighten_shortcode__load_template($atts) {
	extract(shortcode_atts(array('name' => ''), $atts));
	ob_start();
	get_template_part($name);
	return ob_get_clean();
}

add_shortcode('site_url','enlighten_shortcode__site_url');
function enlighten_shortcode__site_url($atts) {
    $url = "";
    if (isset($atts[0])) {
    	$url = $atts[0];
    }
    return site_url($url);
}

add_shortcode('permalink','enlighten_shortcode__permalink');
function enlighten_shortcode__permalink($atts) {
    extract(shortcode_atts(array('id' => 0), $atts));
    return get_permalink($id);
}
