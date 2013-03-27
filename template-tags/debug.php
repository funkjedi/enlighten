<?php

add_action('init', 'enlighten_enable_output_buffering');
function enlighten_enable_output_buffering() {
    ob_start();
}


function vardump() {
    require_once dirname(__FILE__) . '/../vendor/php-ref/ref.php';
    while (ob_get_level()) {
        ob_end_clean();
    }
    $args = func_get_args();
    call_user_func_array('r', $args);
    exit;
}


function enlighten_log($message) {
    print "<!-- $message -->";
}
