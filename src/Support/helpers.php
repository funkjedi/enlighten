<?php

function enlighten($make = null, $parameters = []){
	if (is_null($make)) {
		return \Enlighten\Application::getInstance();
	}
	return \Enlighten\Application::getInstance()->make($make, $parameters);
}

function enlighten_get_option($group, $name, $default = null){
	$options = get_option("enlighten-options-{$group}");
	if (isset($options[$name]) === true) {
		return $options[$name];
	}
	return $default;
}

function enlighten_register_ajax(array $actions){
	enlighten('kernel')->registerActions($actions);
}

function enlighten_schema($filename){
	global $wpdb;
	if (get_option('enlighten-schema-revision') !== sha1_file($filename)) {
		require_once ABSPATH.'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();
		include $filename;
		update_option('enlighten-schema-revision', sha1_file($filename), true);
	}
}

function enlighten_view($name, array $data = array()){
	return enlighten('view')->make($name, $data);
}
