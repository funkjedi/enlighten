<?php

namespace Enlighten;

class Plugin
{
	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		new \Enlighten\Admin\Mailer;
		new \Enlighten\Admin\Sass;
		new \Enlighten\SassCompiler;
		new \Enlighten\Shortcodes;
		new \Enlighten\Widgets;

		add_action('init', array($this, 'whitelistIpAddresses'));

		// Register ajax handlers
		foreach (apply_filters('enlighten_ajax_actions', array()) as $action) {
			$action->registerActionHandler();
		}

		//$this->checkForUpgrade();
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
	 * The library periodically checks the URL to see if there's a new version
	 * available and displays an update notification to the user if necessary.
	 */
	public function checkForUpgrade()
	{
		$pucGitHubChecker = \PucFactory::getLatestClassVersion('PucGitHubChecker');
		(new $pucGitHubChecker('https://github.com/funkjedi/enlighten/', __FILE__, 'master'));
	}
}
