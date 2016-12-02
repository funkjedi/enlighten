<?php

namespace Enlighten\Foundation;

use BadMethodCallException;
use Enlighten\Foundation\Action;

class Dispatcher
{
	/**
	 * @var array
	 */
	protected $actions = array();

	/**
	 * @var \Enlighten\Foundation\Dispatcher
	 */
	protected static $instance;

	/**
	 * Create an instance.
	 *
	 * @param array
	 */
	public function __construct(array $actions = array())
	{
		$this->registerActions($actions);
	}

	/**
	 * Retrieve the request.
	 *
	 * @return \Enlighten\Foundation\Dispatcher
	 */
	public static function getInstance(array $actions = array())
	{
		if (!self::$instance) {
			self::$instance = new self;
		}

		if (count($actions)) {
			self::$instance->registerActions($actions);
		}

		return self::$instance;
	}

	/**
	 * Register handlers with Wordpress.
	 *
	 * @param \Enlighten\Foundation\Action
	 */
	public function registerAction(Action $action)
	{
		$name = $action->getActionName();

		add_action("wp_ajax_{$name}", array($this, "enlighten_dispatcher_{$name}"));

		if ($action->isPublic()) {
			add_action("wp_ajax_nopriv_{$name}", array($this, "enlighten_dispatcher_{$name}"));
		}

		$this->actions[] = $action;
	}

	/**
	 * Register handlers with Wordpress.
	 *
	 * @param array<\Enlighten\Foundation\Action>
	 */
	public function registerActions(array $actions = array())
	{
		foreach ($actions as $action) {
			$this->registerAction($action);
		}
	}

	/**
	 * Dispatch Wordpress ajax requests.
	 */
	public function __call($name, $args = null)
	{
		$name = preg_replace('/^enlighten_dispatcher_/', '', $name);

		foreach ($this->actions as $action) {
			if ($name === $action->getActionName()) {
				try {
					return $action->handle();
				} catch (Exception $e) {
					return $action->error($e);
				}
			}
		}

		throw new BadMethodCallException($name);
	}
}
