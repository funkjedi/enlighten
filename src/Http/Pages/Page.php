<?php

namespace Enlighten\Http\Pages;

use Enlighten\Http\Controller;

abstract class Page extends Controller
{
	/**
	 * The capability required for this page's menu item to be displayed to the user.
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * The text to be displayed in the title tags of the page when the menu is selected.
	 *
	 * @string
	 */
	protected $pageTitle = '';

	/**
	 * The text to be used for the menu.
	 *
	 * @string
	 */
	protected $menuTitle = '';

	/**
	 * The slug representing this page.
	 *
	 * @var string
	 */
	protected $slug = null;

	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		parent::__construct();

		if (!$this->pageTitle) {
			$this->menuTitle = $this->pageTitle;
		}

		add_action('admin_menu', array($this, 'registerPage'));
		add_action('admin_init', array($this, 'registerSettings'));
	}

	/**
	 * Register the options page with the Wordpress menu.
	 */
	public function registerPage()
	{
		if ($this->menuTitle) {
			add_options_page($this->pageTitle, $this->menuTitle, $this->capability, $this->getSlug(), array($this, 'handle'));
		}
	}

	/**
	 * Register settings and default fields.
	 */
	public function registerSettings()
	{
		//
	}

	/**
	 * Render the options page.
	 */
	abstract public function handle();

	/**
	 * Get the a slug representing this page.
	 *
	 * @return string
	 */
	public function getSlug()
	{
		if (strlen($this->slug)) {
			return $this->slug;
		}

		$action = Str::slug(get_class($this));

		return sanitize_key($action);
	}
}
