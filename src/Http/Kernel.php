<?php

namespace Enlighten\Http;

use BadMethodCallException;
use Enlighten\Application;
use Enlighten\Http\Ajax\Action;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Kernel implements KernelContract
{
	/**
	 * The application implementation.
	 *
	 * @var \Enlighten\Application
	 */
	protected $app;

	/**
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		$this->app = Application::getInstance();
	}

	/**
	 * Register handlers with Wordpress.
	 *
	 * @param \Enlighten\Foundation\Action
	 */
	public function registerAction(Action $action)
	{
		$name = $action->getActionName();

		add_action("wp_ajax_{$name}", array($this, "wp_ajax_{$name}"));

		if ($action->isPublic()) {
			add_action("wp_ajax_nopriv_{$name}", array($this, "wp_ajax_{$name}"));
		}

		$this->actions[] = $action;
	}

	/**
	 * Register handlers with Wordpress.
	 *
	 * @param array<\Enlighten\Http\Ajax\Action>
	 */
	public function registerActions(array $actions = array())
	{
		foreach ($actions as $action) {
			$this->registerAction($action);
		}
	}

	/**
	 * Bootstrap the application for HTTP requests.
	 *
	 * @return void
	 */
	public function bootstrap()
	{
		if (! $this->app->hasBeenBootstrapped()) {
			$this->app->bootstrapWith($this->bootstrappers());
		}
	}

	/**
	 * Handle an incoming HTTP request.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle($request)
	{
		//
	}

	/**
	 * Handle an incoming AJAX request.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request
	 * @param \Englighten\Http\Ajax\Action
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handleAction(Request $request, Action $action)
	{
		try {
			$this->bootstrap();
			$response = $action->handle();
		} catch (NotFoundHttpException $e) {
			$response = $this->respondWith404();
		} catch (Exception $e) {
			$response = $action->error($e);
		}

		return $this->prepareResponse($request, $response);
	}

	/**
	 * Handle an incoming AJAX request.
	 *
	 * @param string
	 * @param null
	 */
	public function __call($name, $args = null)
	{
		$name = preg_replace('/^wp_ajax_/', '', $name);

		foreach ($this->actions as $action) {
			if ($name === $action->getActionName()) {
				$response = $this->handleAction($this->app->request, $action);
				$response->send();
				$this->terminate($this->app->request, $response);
				exit;
			}
		}

		throw new BadMethodCallException($name);
	}

	/**
	 * Perform any final actions for the request lifecycle.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request
	 * @param \Symfony\Component\HttpFoundation\Response
	 * @return void
	 */
	public function terminate($request, $response)
	{
		$this->app->terminate();
	}

	/**
	 * Create a response instance from the given value.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @param  mixed  $response
	 * @return \Illuminate\Http\Response
	 */
	public function prepareResponse($request, $response)
	{
		if ($response instanceof PsrResponseInterface) {
			$response = (new HttpFoundationFactory)->createResponse($response);
		} elseif (! $response instanceof SymfonyResponse) {
			$response = new Response($response);
		}

		return $response->prepare($request);
	}

	/**
	 * Handle 404 through Wordpress.
	 */
	protected function respondWith404()
	{
		global $wp_query;

		$wp_query->set_404();

		ob_start();
		require TEMPLATEPATH.'/404.php';
		return ob_get_clean();
	}

	/**
	 * Get the Laravel application instance.
	 *
	 * @return \Enlighten\Application
	 */
	public function getApplication()
	{
		return $this->app;
	}
}
