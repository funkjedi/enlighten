<?php

namespace Enlighten\Http\Pages;

use Enlighten\Http\Controller;
use Illuminate\Support\Str;

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
	 * The slug name to refer to this menu by.
	 *
	 * @string
	 */
	protected $menuTitle = '';

	/**
	 * The slug name to refer to this menu by.
	 *
	 * @var string
	 */
	protected $slug = null;

	/**
	 * The slug name for the parent menu.
	 *
	 * @var string
	 */
	protected $parentSlug = null;

	/**
	 * The URL to the icon to be used for this menu..
	 *
	 * @var string
	 */
	protected $icon = '';

	/**
	 * The position in the menu order this one should appear..
	 *
	 * @var string
	 */
	protected $position = null;

	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		parent::__construct();

		if (!$this->pageTitle) {
			$this->pageTitle = $this->menuTitle;
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
			if ($this->parentSlug) {
				add_submenu_page($this->parentSlug, $this->pageTitle, $this->menuTitle, $this->capability, $this->getSlug(), array($this, 'render'));
			} else {
				add_menu_page($this->pageTitle, $this->menuTitle, $this->capability, $this->getSlug(), array($this, 'render'), $this->icon, $this->position);
			}
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
	 * Render the page.
	 */
	abstract public function handle();

	/**
	 * Render the page.
	 */
	public function render()
	{
		echo (string) $this->handle();
	}

	/**
	 * Get the menu slug representing this page.
	 *
	 * @return string
	 */
	public function getSlug()
	{
		if (strlen($this->slug)) {
			return $this->slug;
		}

		$action = Str::slug(str_replace('\\','_',get_class($this)));

		return sanitize_key($action);
	}
}
