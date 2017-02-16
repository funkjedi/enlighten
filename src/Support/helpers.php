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

function enlighten_view($name, array $data = array()){
	return enlighten('view')->make($name, $data);
}
