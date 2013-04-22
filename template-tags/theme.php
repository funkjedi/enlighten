<?php

function mdetect($method) {
    static $mdetect;
    if (!$mdetect) {
        require_once dirname(__FILE__) . '/../vendor/mdetect.php';
        $mdetect = new uagent_info();
    }
    return $mdetect->$method();
}

function get_theme_url($path) {
    return get_template_directory_uri() . '/' . ltrim($path, '/');
}

function the_theme_url($path) {
    print get_theme_url($path);
}

function the_language_switcher($format = '<a class="languageswitcher" href="{url}" hreflang="{code}" title="{name}"><span>{name}</span></a>') {
	global $q_config;
	if (function_exists('qtrans_getSortedLanguages')) {
		foreach(qtrans_getSortedLanguages() as $language) {
			if ($language != $q_config['language']) {
				print strtr($format, array(
					'{url}'  => qtrans_convertURL('', $language),
					'{code}' => $language,
					'{name}' => $q_config['language_name'][$language],
				));
				break;
			}
		}
	}
}
