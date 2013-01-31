<?php
/*
 Wordpress Steak
 http://github.com/funkjedi/wps

 Copyright 2011, Tim Robertson
 http://opensource.org/licenses/mit-license.php The MIT License
*/

class WPFormHelper {

	public $params = array();
	public $errors = array();
	protected $validations = array();


	public function __construct() {
		$this->params = array_merge(
			(array) filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING),
			(array) filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING));
	}

	public static function init() {
		$GLOBALS['wpfh'] = new self();
	}

	// validate a field as required
	function required($key, $message) {
		if ($this->params) {
			if (!isset($this->params[$key]) or empty($this->params[$key]))
				$this->errors[$key] = apply_filters('wpformhelper.error_message', $message, $key);
		}
	}

}

WPFormHelper::init();


function has_param($key) {
	global $wpfh;
	return isset($wpfh->params[$key]);
}

function has_params($params) {
	global $wpfh;
	foreach (explode(',', $params) as $key) {
		if (has_param($key) === false) {
			return false;
		}
	}
	return true;
}

function get_param($key) {
	global $wpfh;
	return isset($wpfh->params[$key]) ? $wpfh->params[$key] : '';
}

function get_params() {
	global $wpfh;
	return $wpfh->params;
}

function the_param($key) {
	echo get_param($key);
}

function the_param_checked($key, $value) {
	if (get_param($key) == $value)
		print ' checked="checked"';
}

function the_param_selected($key, $value) {
	if (get_param($key) == $value)
		print ' selected="selected"';
}

function the_param_error($key) {
	global $wpfh;
	if (isset($wpfh->errors[$key]))
		printf('<span class="help-block error">%s</span>', $wpfh->errors[$key]);
}

function the_param_has_error($key) {
	global $wpfh;
	if (isset($wpfh->errors[$key]))
		print 'error';
}
