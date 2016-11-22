<?php

namespace Enlighten;

use WP_Query;

class Loop
{
	/**
	 * Create an instance.
	 *
	 * @param mixed
	 * @param \WP_Query|null
	 *
	 * @global \WP_Post
	 */
	public function __construct($posts, $query = null)
	{
		global $post;

		$this->posts = $posts;
		$this->post_count = count($posts);
		$this->current_post = -1;
		$this->original_post = $post;
		$this->query = $query ?: (object)array('max_num_pages' => 1);
	}

	/**
	 * Determines whether there are more posts available in the loop.
	 *
	 * @return bool
	 */
	public function have_posts()
	{
		if ($this->current_post + 1 < $this->post_count) {
			return true;
		}

		$this->reset();

		return false;
	}

	/**
	 * Sets up the current post.
	 *
	 * Retrieves the next post, sets up the post, sets the 'in the loop'
	 * property to true.
	 *
	 * @global \WP_Post
	 */
	public function the_post()
	{
		global $post;

		$this->current_post += 1;
		setup_postdata($post = $this->posts[$this->current_post]);
	}

	/**
	 * Restores the $post global to the original post.
	 *
	 * @global \WP_Post
	 */
	public function reset()
	{
		global $post;

		$this->current_post = -1;
		if ($this->original_post) {
			setup_postdata($post = $this->original_post);
		}
	}

	/**
	 * Retrieve the posts based on query variables.
	 *
	 * @param mixed
	 * @return \Enlighten\Loop
	 *
	 * @global \WP_Post
	 */
	public static function create($args = array())
	{
		global $post, $wp_query;

		// use get_post()
		if (is_numeric($args)) {
			$args = get_post($args);
			return new self(array($args));
		}

		// use existing an \WP_Query
		if (is_object($args) and $args instanceof WP_Query) {
			return new self($args->posts, $args);
		}

		// check for an existing array of posts
		if (is_array($args) and isset($args[0]->ID)) {
			return new self($args);
		}

		// create a new \WP_Query using get_post defaults
		// as the defaults for the new query
		if (is_array($args)) {
			$wpq = new WP_Query(wp_parse_args($args, array(
				'numberposts'      => 5,
				'offset'           => 0,
				'category'         => 0,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'include'          => array(),
				'exclude'          => array(),
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => 'post',
				'suppress_filters' => true
			)));
			return new self($wpq->posts, $wpq);
		}

		// use the global \WP_Query
		if (is_object($wp_query) and $wp_query instanceof WP_Query) {
			return new self($wp_query->posts, $wp_query);
		}

		// use the post
		return new self(array($post));
	}
}
