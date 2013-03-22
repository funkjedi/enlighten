<?php

require_once dirname(__FILE__) . '/widgets/advanced-text.php';
require_once dirname(__FILE__) . '/widgets/load-template.php';

add_action('widgets_init', 'enlighten_register_widgets');
function enlighten_register_widgets() {
	register_widget('Advanced_Text_Widget');
	register_widget('Load_Template_Widget');
}
