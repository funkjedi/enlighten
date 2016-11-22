<?php

namespace Enlighten;

class Widgets
{
	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		add_action('widgets_init', array($this, 'widgets_init'));
	}

	/**
	 * Fires after all default WordPress widgets have been registered.
	 */
	public function widgets_init()
	{
		register_widget('Enlighten\Widgets\TemplateWidget');
	}
}
