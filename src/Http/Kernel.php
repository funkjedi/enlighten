<?php

namespace Enlighten\Http;

use BadMethodCallException;
use Enlighten\Application;
use Enlighten\Http\Ajax\Action;
use Exception;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Kernel implements KernelContract
{
	use RouteDependencyResolverTrait;

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
	 * @param \Enlighten\Http\Ajax\Action
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
	 * Register handlers with Wordpress.
	 *
	 * @param array<\Enlighten\Http\Pages\Page>
	 */
	public function registerPages(array $pages = array())
	{
		foreach ($pages as $page) {
			//
		}
	}

	/**
	 * Bootstrap the application for HTTP requests.
	 *
	 * @return void
	 */
	public function bootstrap()
	{
		$this->app->boot();
		$this->app['router']->boot();
	}

	/**
	 * Handle an incoming HTTP request.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle($request)
	{
		global $wp;

		try {
			$params = explode('|', $wp->query_vars['enlighten_route']);

			if (count($params) < 2) {
				throw new NotFoundHttpException;
			}

			$controller = array_shift($params);

			if ($controller[0] !== '\\') {
				$controller = 'App\\Http\\Controllers\\'.$controller;
			}

			$action = array_shift($params);

			if (! method_exists($controller, $action)) {
				throw new NotFoundHttpException;
			}

			$this->bootstrap();
			$response = $this->call($this->app->make($controller), $action, $params);
		}
		catch (ModelNotFoundException $e) {
			$response = $this->respondWith404();
		}
		catch (NotFoundHttpException $e) {
			$response = $this->respondWith404();
		}
		catch (Exception $e) {
			$response = $action->error($e);
		}

		return $this->prepareResponse($request, $response);
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
			$response = $this->call($action, 'handle');
		}
		catch (ModelNotFoundException $e) {
			$response = $this->respondWith404();
		}
		catch (NotFoundHttpException $e) {
			$response = $this->respondWith404();
		}
		catch (Exception $e) {
			$response = $action->error($e);
		}

		return $this->prepareResponse($request, $response);
	}

    /**
     * Call the given controller instance method.
     *
     * @param \Enlighten\Http\Controller
     * @param string
     * @param array
     * @return mixed
     */
    protected function call($instance, $method, array $parameters = [])
    {
        $parameters = array_filter($parameters, function($parameter) {
            return !is_null($parameter);
        });

        $parameters = $this->resolveClassMethodDependencies(
            $parameters, $instance, $method
		);

        return $instance->callAction($method, $parameters);
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
