<?php

add_action('widgets_init', 'enlighten_register_widgets');
function enlighten_register_widgets() {
	register_widget('Load_Template');
}


class Load_Template extends WP_Widget {
  function __construct() {
		parent::WP_Widget(false, 'Load Template', array('description' => 'Outputs a template using load_template().'));
	}
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		ob_start();
		get_template_part($instance['title']);
		$output = trim(ob_get_clean());
		if (empty($output) === false) {
			echo str_replace('class="', 'class="' . preg_replace('#[^a-z0-9_-]+#i', '', $instance['title']) . ' ', $before_widget) . $output . $after_widget;
		}
	}
	function form($instance) {
		?>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="hidden" value="<?php echo esc_attr($instance['title']); ?>" />
			<label for="<?php echo $this->get_field_id('title'); ?>">Template:</label>
			<select id="<?php echo $this->get_field_id('title'); ?>"	name="<?php echo $this->get_field_name('title'); ?>">
				<option value="">-None-</option>
				<?php foreach(wp_get_theme()->get_files('php') as $template => $filename): $filename = substr(basename($filename), 0, -4); ?>
					<option value="<?php echo $filename; ?>"<?php if ($instance['title'] === $filename) echo 'selected="selected"'; ?>><?php echo $filename; ?></option>
				<?php endforeach; ?>
			</select>
		<?php
	}
}
