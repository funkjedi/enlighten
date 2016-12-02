<?php

namespace Enlighten\Foundation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class Action
{
	/**
	 * @var boolean
	 */
	protected $public = true;

	/**
	 * @var string
	 */
	protected $action = null;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected static $request;

	/**
	 * @var \Symfony\Component\HttpFoundation\Session\Session
	 */
	protected static $session;

	/**
	 * Handle an ajax request.
	 *
	 * @return void
	 */
	abstract public function handle();

	/**
	 * Create an instance.
	 */
	public function __construct()
	{

	}

	/**
	 * Retrieve a POST varaiable.
	 *
	 * @param string
	 * @param mixed
	 */
	public function request($key, $default = null)
	{
		if (is_null($key)) {
			return self::getRequest();
		}

		return self::getRequest()->request->filter($key, $default, FILTER_SANITIZE_STRING);
	}

	/**
	 * Retrieve the request.
	 *
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function static getRequest()
	{
		if (!self::$request) {
			self::$request = Request::createFromGlobals();
		}

		return self::$request;
	}

	/**
	 * Retrieve a QUERY varaiable.
	 *
	 * @param string
	 * @return mixed
	 */
	public function query($key)
	{
		return self::$request->query->get($key);
	}

	/**
	 * Retrieve a FILE varaiable.
	 *
	 * @param string
	 * @return \Symfony\Component\HttpFoundation\File|null
	 */
	public function file($key)
	{
		return self::$request->files->get($key);
	}

	/**
	 * Retrieve a SERVER varaiable.
	 *
	 * @param string
	 * @return mixed
	 */
	public function server($key)
	{
		return self::$request->server->get($key);
	}

	/**
	 * Retrieve a SERVER varaiable.
	 *
	 * @param string
	 * @return mixed
	 */
	public function session($key)
	{
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				self::getSession()->set($k, $v);
			}
			return;
		}

		if (is_null($key)) {
			return self::getSession();
		}

		return self::getSession()->get($key);
	}

	/**
	 * Retrieve the session.
	 *
	 * @return \Symfony\Component\HttpFoundation\Session
	 */
	public function static getSession()
	{
		if (!self::$session) {
			self::$session = new Session;
			self::$session->start();
		}

		return self::$session;
	}

	/**
	 * Send an HTTP response.
	 *
	 * @param mixed
	 * @param integer
	 */
	public function response($content, $status = 200)
	{
		if (is_array($content)) {
			return $this->json($content, $status);
		}
		if (is_object($content)) {
			if (method_exists($content, '__toString')) {
				return $this->response((string)$content, $status);
			}
			if (is_a($content, 'Illuminate\Contracts\Support\Jsonable')) {
				return $this->response($content->toJson(), $status);
			}
		}
		http_response_code($status);
		print $content;
		exit;
	}

	/**
	 * Send an HTTP JSON response.
	 *
	 * @param mixed
	 * @param integer
	 */
	public function json($content, $status = 200)
	{
		http_response_code($status);
		wp_send_json($content);
		exit;
	}

	/**
	 * Send an HTTP response.
	 *
	 * @param mixed
	 */
	public function success($content = '', $status = 200)
	{
		return $this->json(array('success' => true, 'data' => $content), $status);
	}

	/**
	 * Send an HTTP response.
	 *
	 * @param mixed
	 */
	public function error($content, $status = 400)
	{
		if (is_a($content, 'Exception')) {
			$content = $content->getMessage();
		}

		return $this->json(array('error' => $content), $status);
	}

	/**
	 * Send a 404 response thru Wordpress.
	 */
	public function abort()
	{
		global $wp_query;

		$wp_query->set_404();

		header('HTTP/1.0 404 Not Found');
		require TEMPLATEPATH.'/404.php';
		exit;
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
		return (new View($name, $data))->render();
	}

	/**
	 * Whether this action is public or only for authenticated users.
	 *
	 * @return boolean
	 */
	public function isPublic()
	{
		return $this->public;
	}

	/**
	 * Get the action for this instance.
	 *
	 * @return string
	 */
	public function getActionName()
	{
		if (strlen($this->action)) {
			return $this->action;
		}

		$action = preg_replace('/^.+\\\\Ajax\\\\(.+)Action$/u', '$1', get_class($this));
		$action = strtolower(preg_replace('/(?<!^)([A-Z])/u', '_$1', str_replace('\\','',$action)));

		return sanitize_key($action);
	}

	/**
	 * Register handler with Wordpress.
	 */
	public function registerActionHandler()
	{
		$action = $this->getActionName();

		add_action("wp_ajax_{$action}", array($this, 'handle'));

		if ($this->isPublic()) {
			add_action("wp_ajax_nopriv_{$action}", array($this, 'handle'));
		}
	}
}
