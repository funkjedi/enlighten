<?php

function add_post_thumbnail($name, $id, $post_types = array('page', 'post')) {
    if (class_exists('MultiPostThumbnails')) {
        foreach ($post_types as $post_type) {
            new MultiPostThumbnails(array('label' => $name, 'id' => $id, 'post_type' => $post_type));
        }
    }
}

function has_post_thumbnail_src($multi_post_thumbnail = '') {
    global $post;
    $attachmentID = null;
    if (class_exists('MultiPostThumbnails')) {
        if ($multi_post_thumbnail) {
            $attachmentID = MultiPostThumbnails::get_post_thumbnail_id(get_post_type($post), $multi_post_thumbnail, $post->ID);
        }
        elseif (function_exists('qtrans_getLanguage')) {
            $attachmentID = MultiPostThumbnails::get_post_thumbnail_id(get_post_type($post), 'featured-image-' . qtrans_getLanguage(), $post->ID);
        }
    }
    if (!isset($attachmentID) or !$attachmentID) {
        $attachmentID = get_post_thumbnail_id($post->ID);
    }
    return $attachmentID;
}

function the_post_thumbnail_src($size = 'full', $background_image = false, $multi_post_thumbnail = '') {
    if ($image = wp_get_attachment_image_src(has_post_thumbnail_src($multi_post_thumbnail), $size)) {
        echo $background_image ? "background-image: url({$image[0]});" : $image[0];
    }
}

function the_post_thumbnail_caption($multi_post_thumbnail = '') {
    if ($attachmentID = has_post_thumbnail_src($multi_post_thumbnail)) {
        if ($post = get_post($attachmentID)) {
            print $post->post_excerpt;
        }
    }
}

function sub_field_index() {
    global $acf_field;
    return $acf_field[count($acf_field) - 1]['row'];
}

function the_sub_field_index() {
    echo sub_field_index();
}



class Enlighten_Loop {
    function __construct($posts, $max_num_pages = 1) {
        $this->posts = $posts;
        $this->post_count = count($posts);
        $this->current_post = -1;
        $this->original_post = $GLOBALS['post'];
        $this->max_num_pages = $max_num_pages;
    }
    function have_posts() {
        if ($this->current_post + 1 < $this->post_count) {
            return true;
        }
        $this->reset();
        return false;
    }
    function the_post() {
        $this->current_post += 1;
        setup_postdata($GLOBALS['post'] = $this->posts[$this->current_post]);
    }
    function reset() {
        $this->current_post = -1;
        setup_postdata($GLOBALS['post'] = $this->original_post);
    }
    function the_pagination(array $args = array()) {
        print bootstrap_paginate_links(array_merge(array(
            'base'    => str_replace(999999999, '%#%', get_pagenum_link(999999999)),
            'format'  => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total'   => $this->max_num_pages
        ), $args));
    }
}

function enlighten_loop($args = null) {
    if (isset($args)) {
        // use existing an WP_Query
        if (is_object($args) and get_class($args) === 'WP_Query') {
            $loop = new Enlighten_Loop($args->posts, $args->max_num_pages);
        }
        // check for an existing array of posts
        elseif (is_array($args) and isset($args[0]->ID)) {
            $loop = new Enlighten_Loop($args);
        }
        // create a new WP_Query using get_post defaults
        // as the defaults for the new query
        else {
            $wpq = new WP_Query(wp_parse_args($args, array(
                'post_status'         => 'publish',
                'posts_per_page'      => 5,
                'offset'              => 0,
                'cat'                 => 0,
                'orderby'             => 'post_date',
                'order'               => 'DESC',
                'include'             => array(),
                'exclude'             => array(),
                'meta_key'            => '',
                'meta_value'          => '',
                'post_type'           => 'post',
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
                'suppress_filters'    => true,
            )));
            $loop = new Enlighten_Loop($wpq->posts, $wpq->max_num_pages);
        }
    }
    else {
        // use the current post
        $loop = new Enlighten_Loop(array($GLOBALS['post']));
    }
    return $loop;
}


function the_content_from($page_id, $suppress_filters = false) {
    if (is_numeric($page_id)) {
        $post = get_page($page_id);
    }
    else {
        $post = get_page_by_title($page_id);
    }
    if ($post) {
        echo $suppress_filters
            ? $post->post_content
            : apply_filters("the_content", $post->post_content);
    }
}

