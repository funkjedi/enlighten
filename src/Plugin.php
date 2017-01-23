<?php

namespace Enlighten;

class Plugin
{
	/**
	 * @var \Enlighten\Application
	 */
	protected $app;

	/**
	 * @var string
	 */
	protected $pluginFile;

	/**
	 * Create an instance.
	 */
	public function __construct($pluginFile)
	{
		$this->pluginFile = $pluginFile;

		$this->app = new Application(dirname($pluginFile));

		$this->app->instance('plugin', $this);

		add_action('init',         array($this, 'whitelistIpAddresses'));
		add_action('widgets_init', array($this, 'registerWidgets'));

		new \Enlighten\WP\Shortcodes;
	}

	/**
	 * You may need to whitelist your IP address on the server if you want to
	 * connect the the server remotely in some situations.
	 */
	public function whitelistIpAddresses()
	{
		if (class_exists('wp_basic_auth')) {
			$addresses = array('::1', '127.0.0.1');

			if (in_array($_SERVER['REMOTE_ADDR'], apply_filters('wp_basic_auth_whitelist', $addresses))) {
				remove_action('template_redirect', [\wp_basic_auth::$instance, 'basic_auth'], 1);
			}
		}
	}

	/**
	 * Fires after all default WordPress widgets have been registered.
	 */
	public function registerWidgets()
	{
		register_widget('Enlighten\WP\Widgets\TemplateWidget');
	}

	/**
	 * Get the filesystem directory path relative to the plugin.
	 *
	 * @param string
	 * @return string
	 */
	public function path($path = '')
	{
		return plugin_dir_path($this->pluginFile) . ltrim($path, DIRECTORY_SEPARATOR);
	}

	/**
	 * Get the URL relative to the plugin.
	 *
	 * @param string
	 * @return string
	 */
	public function url($path = '')
	{
		return plugin_dir_url($this->pluginFile) . ltrim($path, DIRECTORY_SEPARATOR);
	}
}
