<?php

namespace Enlighten;

class Shortcodes
{
	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		add_shortcode('section', array($this, 'sectionShortcode'));
	}

	/**
	 * Render section templates.
	 *
	 * @param array
	 */
	function sectionShortcode(array $atts)
	{
		if (empty($atts[0]) === false) {
			ob_start();
			get_template_part("sections/$atts[0]");
			return ob_get_clean();
		}
	}
}
