<?php

namespace Enlighten\Http\Ajax;

use Enlighten\Http\Controller;
use Illuminate\Support\Str;

class Action extends Controller
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
	public function handle()
	{
		//
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
		$action = Str::snake(str_replace('\\','',$action));

		return sanitize_key($action);
	}
}
