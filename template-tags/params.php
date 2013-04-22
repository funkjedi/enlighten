<?php

function has_param($key) {
	return isset($_REQUEST[$key]);
}

function has_params($params) {
	foreach (explode(',', $params) as $key) {
		if (has_param($key) === false) {
			return false;
		}
	}
	return true;
}

function get_param($key, $default_value = '') {
	return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default_value;
}

function get_params($params) {
	$values = array();
	foreach (explode(',', $params) as $key) {
		$values[$key] = get_param($key);
	}
	return $values;
}

function the_param($key) {
	echo get_param($key);
}

function the_param_checked($key, $value) {
	print 'value="' . $value . '"';
	if (get_param($key) === $value) {
		print ' checked="checked"';
	}
}

function the_param_selected($key, $value) {
	print 'value="' . $value . '"';
	if (get_param($key) == $value) {
		print ' selected="selected"';
	}
}


$GLOBALS['enlighten_param_errors'] = array();

function the_param_error($key) {
	global $enlighten_param_errors;
	if (isset($enlighten_param_errors[$key])) {
		print '<span class="help-block error">' . $enlighten_param_errors[$key] . '</span>';
	}
}

function the_param_has_error($key) {
	global $enlighten_param_errors;
	if (isset($enlighten_param_errors[$key])) {
		print 'error';
	}
}

function param_required($key, $message = 'Required') {
	global $enlighten_param_errors;
	if (!isset($_REQUEST[$key]) or empty($_REQUEST[$key])) {
		$enlighten_param_errors[$key] = apply_filters('enlighten_param_error', $message, $key);
		return false;
	}
	return true;
}
