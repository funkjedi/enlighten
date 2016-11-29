<?php


function enlighten_get_option($group, $name, $default = null){
	$options = get_option("enlighten-options-{$group}");
	if (isset($options[$name]) === true) {
		return $options[$name];
	}
	return $default;
}

function enlighten_register_ajax(array $actions){
	foreach ($actions as $action) {
		$action->registerActionHandler();
	}
}


if (!function_exists('dd')):
	function dd(){
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		if (!extension_loaded('xdebug')) {
			header('Content-Type: text/plain');
			header('Pragma: no-cache');
			header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate, proxy-revalidate');
			header('Expires: Tue, 04 Sep 2012 05:32:29 GMT');
		}
		foreach (func_get_args() as $x) {
			var_dump($x);
		}
		exit;
	}
endif;
