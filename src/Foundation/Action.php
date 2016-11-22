<?php

namespace Enlighten\Foundation;

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
		// $this->registerActionHandler();
	}

	/**
	 * Retrieve a POST varaiable.
	 *
	 * @param string
	 * @param mixed
	 */
	public function request($key, $default = null)
	{
		$value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);

		if (is_null($default) === false && empty($value)) {
			return $default;
		}

		return $value;
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
	public function error($content)
	{
		return $this->json(array('error' => $content), 500);
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

		$action = preg_replace('/^Enlighten\\\\Ajax\\\\(.+)Action$/u', '$1', get_class($this));
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
