<?php

/**
 * Fires after all default WordPress widgets have been registered.
 */
add_action('widgets_init', function(){
	register_widget('Enlighten\Widgets\TemplateWidget');
});


/**
 * You may need to whitelist your IP address on the server if you want to
 * connect the the server remotely in some situations.
 */
add_action('init', function(){
	if (class_exists('wp_basic_auth')) {
		$addresses = array('::1', '127.0.0.1');

		if (in_array($_SERVER['REMOTE_ADDR'], apply_filters('wp_basic_auth_whitelist', $addresses))) {
			remove_action('template_redirect', [wp_basic_auth::$instance, 'basic_auth'], 1);
		}
	}
});
