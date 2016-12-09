<?php

namespace Enlighten;

class Plugin
{
	/**
	 * @var \Enlighten\Application
	 */
	protected $app;

	/**
	 * Create an instance.
	 */
	public function __construct($basePath = null)
	{
		$this->app = new Application($basePath);
		$this->app['router'];

		$this->checkForUpgrade();

		add_action('init',         array($this, 'whitelistIpAddresses'));
		add_action('widgets_init', array($this, 'registerWidgets'));

		new \Enlighten\WP\SassCompiler;
		new \Enlighten\WP\Shortcodes;

		new \Enlighten\OptionPages\MailerPage;
		new \Enlighten\OptionPages\SassPage;
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
	 * The library periodically checks the URL to see if there's a new version
	 * available and displays an update notification to the user if necessary.
	 */
	public function checkForUpgrade()
	{
		//
	}
}
