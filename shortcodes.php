<?php

function wpenlighten_camelcase_shortcode_args($args) {
	$attrs = array();
	foreach ($args as $key => $value) {
		$key = preg_replace_callback('/_([a-z])/', create_function('$c', 'return strtoupper($c[1]);'), $key);
		if ($value === "true") $value = true;
		elseif ($value === "false") $value = false;
		elseif ($value === "null") $value = null;
		elseif (is_numeric($value)) $value = strstr($value, '.') ? floatval($value) : intval($value);
		$attrs[$key] = $value;
	}
	return $attrs;
}


add_shortcode('content_from','shortcode__content_from');
function shortcode__content_from($atts) {
  extract(shortcode_atts(array('page' => 0), $atts));
  ob_start();
  the_content_from($page);
  return ob_get_clean();
}

add_shortcode('get_template_part','shortcode__get_template_part');
function shortcode__get_template_part($atts) {
  extract(shortcode_atts(array('slug' => '', 'name' => ''), $atts));
  ob_start();
  get_template_part($slug, $name);
  return ob_get_clean();
}


add_filter('the_content', 'the_content_shortcode__flexslider', 6);
function the_content_shortcode__flexslider($content) {
	global $shortcode_tags;
	$orig_shortcode_tags = $shortcode_tags;
	remove_all_shortcodes();
	add_shortcode('flexslider', 'shortcode__flexslider');
	$content = do_shortcode($content);
	$shortcode_tags = $orig_shortcode_tags;
	return $content;
}

//add_shortcode('flexslider','shortcode__flexslider', 7);
function shortcode__flexslider($atts, $content = '') {
	$options = shortcode_atts(array(
		'id'                  => 'flexslider' . time(),
		'tags'                => 'featured',
		'max_slides'          => 4,
		'template'            => '',

		'animation'           => 'fade',             //String: Select your animation type, "fade" or "slide"
		'slide_direction'     => 'horizontal',       //String: Select the sliding direction, "horizontal" or "vertical"
		'slideshow'           => true,               //Boolean: Animate slider automatically
		'slideshow_speed'     => 7000,               //Integer: Set the speed of the slideshow cycling, in milliseconds
		'animation_duration'  => 600,                //Integer: Set the speed of animations, in milliseconds
		'direction_nav'       => true,               //Boolean: Create navigation for previous/next navigation? (true/false)
		'control_nav'         => true,               //Boolean: Create navigation for paging control of each clide? Note: Leave true for manualControls usage
		'keyboard_nav'        => true,               //Boolean: Allow slider navigating via keyboard left/right keys
		'mousewheel'          => false,              //Boolean: Allow slider navigating via mousewheel
		'prev_text'           => 'Previous',         //String: Set the text for the "previous" directionNav item
		'next_text'           => 'Next',             //String: Set the text for the "next" directionNav item
		'pause_play'          => false,              //Boolean: Create pause/play dynamic element
		'pause_text'          => 'Pause',            //String: Set the text for the "pause" pausePlay item
		'play_text'           => 'Play',             //String: Set the text for the "play" pausePlay item
		'randomize'           => false,              //Boolean: Randomize slide order
		'slide_to_start'      => 0,                  //Integer: The slide that the slider should start on. Array notation (0 = first slide)
		'animation_loop'      => true,               //Boolean: Should the animation loop? If false, directionNav will received "disable" classes at either end
		'pause_on_action'     => true,               //Boolean: Pause the slideshow when interacting with control elements, highly recommended.
		'pause_on_hover'      => false,              //Boolean: Pause the slideshow when hovering over slider, then resume when no longer hovering
		'use_CSS'             => true,               //Boolean: Override the use of CSS3 Translate3d animations
		'touch'               => true,               //Boolean: Disable touchswipe events
		'controls_container'  => '',                 //Selector: Declare which container the navigation elements should be appended too. Default container is the flexSlider element. Example use would be ".flexslider-container", "#container", etc. If the given element is not found, the default action will be taken.
		'manual_controls'     => '',                 //Selector: Declare custom control navigation. Example would be ".flex-control-nav li" or "#tabs-nav li img", etc. The number of elements in your controlNav should match the number of slides/tabs.
		//'start'             => '',               //Callback: function(slider) - Fires when the slider loads the first slide
		//'before'            => '',               //Callback: function(slider) - Fires asynchronously with each slider animation
		//'after'             => '',               //Callback: function(slider) - Fires after each slider animation completes
		//'end'               => '',               //Callback: function(slider) - Fires when the slider reaches the last slide (asynchronous)

	), $atts);

	// extract the shortcode options from the options which
	// will be passed to the javascript slider
	$id = $options['id'];
	$tags = $options['tags'];
	$max_slides = $options['max_slides'];
	$template = $options['template'];

	unset($options['id'], $options['tags'], $options['max_slides'], $options['template']);

	// camelize the keys in the options array
	$options = wpenlighten_camelcase_shortcode_args($options);
	ob_start(); ?>

		<div id="<?php echo $id; ?>" class="flexslider">


		<?php if (empty($content)): ?>
			<ul class="slides">
			<?php
				$original_post = $GLOBALS['post'];
				$posts = get_posts(array(
					'post_type'      => 'any',
					'tag_slug__in'   => explode(',', $tags),
					'posts_per_page' => $max_slides,
				));
			?>
			<?php foreach ($posts as $post) : setup_postdata($GLOBALS['post'] = $post); ?>
				<?php if ($template): ?>

					<?php get_template_part($template); ?>

				<?php else: ?>

					<li>
						<a href="<?php echo get_permalink(); ?>">
						<?php if (has_post_thumbnail_src()): ?>
							<img class="post-thumbnail" src="<?php echo the_post_thumbnail_src('full'); ?>" alt="<?php the_title(); ?>">
						<?php endif; ?>
						</a>
					</li>

				<?php endif; ?>
			<?php endforeach;?>

			<?php
				// instead of using wp_reset_postdata() we reinstate the original $post;
				setup_postdata($GLOBALS['post'] = $original_post);
			?>
			</ul>
		<?php else: ?>
			<?php echo $content; ?>
		<?php endif; ?>


		</div>
		<script type="text/javascript">
		jQuery(function($) {
			$('#<?php echo $id; ?>').flexslider(<?php echo json_encode($options); ?>);
		});
		</script>

		<?php wp_enqueue_style('flexslider',  plugins_url('assets/javascript/flexslider/flexslider.css', __FILE__)); ?>
		<?php wp_enqueue_script('flexslider', plugins_url('assets/javascript/flexslider/jquery.flexslider-min.js', __FILE__)); ?>

	<?php
	return "[rawr]" . ob_get_clean() ."[/rawr]";
}