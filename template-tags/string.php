<?php

function get_youtube_id($url) {
    $matches = array();
    preg_match('#(?<=(?:v|i)=)[a-zA-Z0-9-]+(?=&)|(?<=(?:v|i)\/)[^&\n]+|(?<=embed\/)[^"&\n]+|(?<=(?:v|i)=)[^&\n]+|(?<=youtu.be\/)[^&\n]+#i', $url, $matches);
    if (isset($matches[0]) and !empty($matches[0])) {
        return trim($matches[0]);
    }
}

function get_vimeo_id($url) {
    $matches = array();
    preg_match( '#https?://(www.vimeo|vimeo)\.com(/|/clip:)(\d+)(.*?)#i', $url, $matches);
    if (isset($matches[3]) and !empty($matches[3])) {
        return $matches[3];
    }
}
