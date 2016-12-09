<?php

namespace Enlighten\WP\Widgets;

use WP_Widget;

class TemplateWidget extends WP_Widget
{
	function __construct() {
		parent::__construct(false, 'Widget Template', array('description' => 'Outputs a widget template from Theme.'));
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		ob_start();
		get_template_part(@$instance['title']);
		$output = trim(ob_get_clean());
		if (empty($output) === false) {
			echo str_replace('class="', 'class="' . preg_replace('#[^a-z0-9_-]+#i', '', @$instance['title']) . ' ', $before_widget) . $output . $after_widget;
		}
	}

	function form($instance) {
		$templates = [];
		foreach(wp_get_theme()->get_files('php') as $template => $filename) {
			$filename = substr(basename($filename), 0, -4);
			if (preg_match('/^widget-/', $filename)) {
				$templates[] = $filename;
			}
		}
		?>
			<input id="<?= $this->get_field_id('title') ?>" name="<?= $this->get_field_name('title') ?>" type="hidden" value="<?= esc_attr(@$instance['title']) ?>" />
			<p>
				<label for="<?= $this->get_field_id('title') ?>">Template:</label>
				<select id="<?= $this->get_field_id('title') ?>"    name="<?= $this->get_field_name('title') ?>">
					<option value="">-None-</option>
					<?php foreach ($templates as $filename): ?>
						<option value="<?= $filename ?>"<?php if (@$instance['title'] === $filename) echo 'selected="selected"' ?>><?= $filename ?></option>
					<?php endforeach ?>
				</select>
			</p>
		<?php
	}
}
