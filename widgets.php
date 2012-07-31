<?php

add_action('widgets_init', 'wpenlighten_register_widgets');
function wpenlighten_register_widgets() {
	register_widget('Load_Template');
}


class Load_Template extends WP_Widget {
  function __construct() {
		parent::WP_Widget(false, 'Load Template', array('description' => 'Outputs a template using load_template().'));
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		ob_start();
		load_template($instance['template'], false);
		$output = trim(ob_get_clean());
		if (empty($output) === false) {
			echo str_replace('class="', 'class="' . preg_replace('#[^a-z0-9_-]+#i', '_', $instance['title']) . ' ', $before_widget);
			echo $output;
			echo $after_widget;
		}
	}

	function update($new_instance, $old_instance) {
		if ($new_instance['template']) {
			$new_instance['title'] = substr(basename($new_instance['template']), 0, -4);
		}
		return $new_instance;
	}

	function form($instance) {
		?>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="hidden" value="<?php echo esc_attr($instance['title']); ?>" /></p>
			<p>
				<label for="<?php echo $this->get_field_id('template'); ?>">Template:</label>
				<select id="<?php echo $this->get_field_id('template'); ?>"	name="<?php echo $this->get_field_name('template'); ?>">
					<option value="">-None-</option>
				<?php foreach(wp_get_theme()->get_files('php') as $template => $filename): ?>
					<option value="<?php echo $filename; ?>"<?php if ($instance['template'] === $filename) echo 'selected="selected"'; ?>><?php echo $template; ?></option>
				<?php endforeach; ?>
				</select>
			</p>
		<?php
	}
}
