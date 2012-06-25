<?php

add_filter('the_content', 'the_content_shortcode__twig', 6);
function the_content_shortcode__twig($content) {
	global $shortcode_tags;
	$orig_shortcode_tags = $shortcode_tags;
	remove_all_shortcodes();
	add_shortcode('twig', 'shortcode__twig');
	$content = do_shortcode($content);
	$shortcode_tags = $orig_shortcode_tags;
	return $content;
}

add_shortcode('twig', 'shortcode__twig');
function shortcode__twig($attr, $content = '') {
	//extract(shortcode_atts(array('page' => 0), $atts));
	return wpenlighten_render_twig($content, array('post' => $GLOBALS['post']));
}

function wpenlighten_render_twig($content, $context = array()) {
	static $twig;
	if (isset($twig) === false) {
		require_once dirname(__FILE__) . '/Twig/Autoloader.php';
		Twig_Autoloader::register();

		$twig = new Twig_Environment(new Twig_Loader_String(), array('cache' => false));
		$twig->registerUndefinedFilterCallback('_wpenlighten_twitUndefinedFilterCallback');
		$twig->registerUndefinedFunctionCallback('_wpenlighten_twitUndefinedFunctionCallback');
		$twig->addGlobal('wp', new WPEnlightenTwigProxy())
	}

	$template = $twig->loadTemplate($content);
	return "[rawr]" . $template->render($context) . "[/rawr]";
}

function _wpenlighten_twitUndefinedFunctionCallback($name) {
	$wordpress_functions = array();

	if (in_array($name, $wordpress_functions) and function_exists($name)) {
		return new Twig_Function_Function($name);
	}
	return false;
}

function _wpenlighten_twitUndefinedFilterCallback($name) {
	$wordpress_functions = array(
		'the_permalink');

	if (in_array($name, $wordpress_functions) and function_exists($name)) {
		return new Twig_Function_Function($name);
	}
	return false;
}

class WPEnlightenTwigProxy {
	public function __call($function, $arguments) {
		$wordpress_functions = array(
			'the_title',
			'the_content',
			'the_excerpt',
			'the_permalink');

		if (in_array($function, $wordpress_functions) and function_exists($function)) {
			return call_user_func_array($function, $arguments);
		}
		trigger_error("call to unexisting function $function", E_USER_ERROR);
	}
}
