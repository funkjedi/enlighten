<?php

namespace Enlighten\Foundation;

use InvalidArgumentException;

class View
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $data;

	/**
	 * Create an instance.
	 *
	 * @param string
	 * @param array
	 */
	public function __construct($name, $data = array())
	{
		$this->name = $name;
		$this->data = $data;

		if (!$this->getViewPath()) {
			throw new InvalidArgumentException;
		}
	}

	/**
	 * Get the path to the view.
	 */
	public function getViewPath()
	{
		return locate_template(array(
			"backend/views/{$this->name}.php",
			"views/{$this->name}.php",
			"{$this->name}.php",
		));
	}

	/**
	 * Render view and return as a string.
	 */
	public function render()
	{
		ob_start();

		extract($this->data, EXTR_SKIP);
		include $this->getViewPath();

		return ob_get_clean();
	}
}
