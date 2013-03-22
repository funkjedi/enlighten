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
