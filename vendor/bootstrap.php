<?php

function bootstrap_pagination($args = array()) {
	global $wp_query;
	print bootstrap_paginate_links(array_merge(array(
		'base'    => str_replace(999999999, '%#%', get_pagenum_link(999999999)),
		'format'  => '?paged=%#%',
		'current' => max(1, get_query_var('paged')),
		'total'   => isset($args['max_num_pages']) ? $args['max_num_pages'] : $wp_query->max_num_pages
	), $args));
}


/*
	Page Navigation

	Original function: wp-includes/general-template.php
	Taken from http://dimox.net/wordpress-pagination-without-a-plugin-wp-pagenavi-alternative/
*/
function bootstrap_paginate_links($args = '') {
	$defaults = array(
		'base'         => '%_%', // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
		'format'       => '?page=%#%', // ?page=%#% : %#% is replaced by the page number
		'total'        => 1,
		'current'      => 0,
		'show_all'     => false,
		'prev_next'    => true,
		'prev_text'    => __('&laquo; Previous'),
		'next_text'    => __('Next &raquo;'),
		'end_size'     => 1,
		'mid_size'     => 2,
		'type'         => 'plain',
		'add_args'     => false, // array of query args to add
		'add_fragment' => ''
	);

	$args = wp_parse_args( $args, $defaults );
	extract($args, EXTR_SKIP);

	// Who knows what else people pass in $args
	$total = (int) $total;
	if ($total < 2)
		return;
	$current  = (int) $current;
	$end_size = 0  < (int) $end_size ? (int) $end_size : 1; // Out of bounds?  Make it the default.
	$mid_size = 0 <= (int) $mid_size ? (int) $mid_size : 2;
	$add_args = is_array($add_args) ? $add_args : false;
	$r = '';
	$page_links = array();
	$n = 0;
	$dots = false;

	if ($prev_next && $current && 1 < $current):
		$link = str_replace('%_%', 2 == $current ? '' : $format, $base);
		$link = str_replace('%#%', $current - 1, $link);
		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
		$link .= $add_fragment;
		$page_links[] = '<li class="prev"><a href="' . esc_url(apply_filters('paginate_links', $link)) . '">' . $prev_text . '</a></li>';
	else:
		$page_links[] = '<li class="prev disabled"><a href="#">' . $prev_text . '</a></li>';
	endif;

	for ($n = 1; $n <= $total; $n++) :
		$n_display = number_format_i18n($n);
		if ($n == $current):
			$page_links[] = "<li class='active'><a href=\"#\" class='page-numbers'>$n_display</a></li>";
			$dots = true;
		else:
			if ($show_all || ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size) || $n > $total - $end_size)):
				$link = str_replace('%_%', 1 == $n ? '' : $format, $base);
				$link = str_replace('%#%', $n, $link);
				if ($add_args)
					$link = add_query_arg( $add_args, $link );
				$link .= $add_fragment;
				$page_links[] = "<li><a class='page-numbers' href='" . esc_url( apply_filters( 'paginate_links', $link ) ) . "'>$n_display</a></li>";
				$dots = true;
			elseif ($dots && !$show_all):
				$page_links[] = '<li class="disabled"><a href="#">...</a></li>';
				$dots = false;
			endif;
		endif;
	endfor;

	if ($prev_next && $current && ($current < $total || -1 == $total)):
		$link = str_replace('%_%', $format, $base);
		$link = str_replace('%#%', $current + 1, $link);
		if ($add_args)
			$link = add_query_arg($add_args, $link);
		$link .= $add_fragment;
		$page_links[] = '<li class="next"><a href="' . esc_url(apply_filters('paginate_links', $link)) . '">' . $next_text . '</a></li>';
	else:
		$page_links[] = '<li class="next disabled"><a href="#">' . $next_text . '</a></li>';
	endif;

	switch ($type):
		case 'array':
			return $page_links;
		case 'list':
			$r .= "<ul class='page-numbers'>\n\t<li>";
			$r .= join("</li>\n\t<li>", $page_links);
			$r .= "</li>\n</ul>\n";
			break;
		default :
			$r = '<div class="pagination"><ul>' . join("\n", $page_links) . '</ul></div>';
			break;
	endswitch;
	return $r;
}
