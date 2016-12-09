<?php

namespace Enlighten\Http;

use Enlighten\Application;
use Enlighten\View\View;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JsonSerializable;

class Controller
{
	/**
	 * @var \Enlighten\Application
	 */
	protected $app;

	/**
	 * @var \Illuminate\Http\Request
	 */
	protected $request;

	/**
	 * Create an instance.
	 */
	public function __construct()
	{
		$this->app = Application::getInstance();
		$this->request = $this->app['request'];
	}

	/**
	 * Get an instance of the current request or an input item from the request.
	 *
	 * @param string
	 * @param mixed
	 * @return \Illuminate\Http\Request|string|array
	 */
	public function request($key = null, $default = null)
	{
		if (is_null($key)) {
			return $this->request;
		}

		return $this->request->input($key, $default);
	}

	/**
	 * Retrieve a query string item from the request.
	 *
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function query($key = null, $default = null)
	{
		return $this->request->query($key, $default);
	}

	/**
	 * Retrieve a file from the request.
	 *
	 * @param string
	 * @param mixed
	 * @return \Symfony\Component\HttpFoundation\File\UploadedFile|array
	 */
	public function file($key = null, $default = null)
	{
		return $this->request->file($key, $default);
	}

	/**
	 * Retrieve a server variable from the request.
	 *
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function server($key = null, $default = null)
	{
		return $this->request->server($key, $default);
	}

	/**
	 * Retrieve a session variable.
	 *
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function session($key = null, $default = null)
	{
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				$this->app['session']->set($k, $v);
			}
			return;
		}

		if (is_null($key)) {
			return $this->app['session'];
		}

		return $this->app['session']->get($key, $default);
	}

	/**
	 * Return a new response.
	 *
	 * @param string
	 * @param int
	 * @param array
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function response($content = '', $status = 200, array $headers = [])
	{
		return new Response($content, $status, $headers);
	}

	/**
	 * Return a new JSON response from the application.
	 *
	 * @param  string|array
	 * @param  int
	 * @param  array
	 * @param  int
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function json($data = [], $status = 200, array $headers = [], $options = 0)
	{
		if ($data instanceof Arrayable && ! $data instanceof JsonSerializable) {
			$data = $data->toArray();
		}

		return new JsonResponse($data, $status, $headers, $options);
	}

	/**
	 * Return a new JSONP response from the application.
	 *
	 * @param  string
	 * @param  string|array
	 * @param  int
	 * @param  array
	 * @param  int
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0)
	{
		return $this->json($data, $status, $headers, $options)->setCallback($callback);
	}

	/**
	 * Send an HTTP response.
	 *
	 * @param string
	 * @param int
	 * @param array
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function success($content = '', $status = 200, array $headers = [])
	{
		$data = ['success' => true];

		if ($content) {
			$data['data'] = $content;
		}

		return $this->json($data, $status, $headers);
	}

	/**
	 * Send an HTTP response.
	 *
	 * @param string
	 * @param int
	 * @param array
	 * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory
	 */
	public function error($content, $status = 400, array $headers = [])
	{
		if (is_a($content, 'Exception')) {
			$content = $content->getMessage();
		}

		return $this->json(['error' => $content], $status, $headers);
	}

	/**
	 * Throw an HttpException with the given data.
	 *
	 * @param int
	 * @param string
	 * @param array
	 * @return void
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function abort($code = 404, $message = '', array $headers = [])
	{
		$this->app->abort($code, $message, $headers);
	}

	/**
	 * Return a rendered a view.
	 *
	 * @param string
	 * @param array|null
	 * @return string
	 */
	public function view($name, array $data = array())
	{
		return $this->app->view->make($name, $data);
	}
}
