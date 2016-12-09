<?php

namespace Enlighten\Http;

use Enlighten\Application;

class Router
{
	/**
	 * The application implementation.
	 *
	 * @var \Enlighten\Application
	 */
	protected $app;

	/**
	 * Create an instance.
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;

		add_action('init',              array($this, 'loadRoutes'));
		add_action('template_redirect', array($this, 'dispatchRoutes'));
	}

	/**
	 * Load routes.
	 */
	public function loadRoutes()
	{
		global $wp;

		$wp->add_query_var('enlighten_route');

		$routes = $this->app->path().'/Http/routes.php';

		if (file_exists($routes)) {
			include $routes;
		}
	}

	/**
	 * Dispatch routes.
	 */
	public function dispatchRoutes()
	{
		global $wp;

		if (empty($wp->query_vars['enlighten_route'])) {
			return;
		}

		$response = $this->app['kernel']->handle($this->app->request);
		$response->send();

		$this->app['kernel']->terminate($this->app->request, $response);
		exit;
	}
}
