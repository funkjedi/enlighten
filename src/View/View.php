<?php

namespace Enlighten\View;

use ArrayAccess;
use BadMethodCallException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Str;

class View implements ArrayAccess, ViewContract
{
	/**
	 * The view factory instance.
	 *
	 * @var \Illuminate\View\Factory
	 */
	protected $factory;

	/**
	 * The name of the view.
	 *
	 * @var string
	 */
	protected $view;

	/**
	 * The array of view data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The path to the view file.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Create an instance.
	 *
	 * @param \Illuminate\View\Factory
	 * @param string
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function __construct(Factory $factory, $view, $path, $data = [])
	{
		$this->view = $view;
		$this->path = $path;
		$this->engine = $engine;
		$this->factory = $factory;

		$this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;
	}

	/**
	 * Get the evaluated contents of the object.
	 *
	 * @return string
	 */
	public function render()
	{
		ob_start();

		extract($this->data, EXTR_SKIP);
		include $this->getViewPath();

		return ob_get_clean();
	}

	/**
	 * Add a piece of data to the view.
	 *
	 * @param  string|array  $key
	 * @param  mixed   $value
	 * @return $this
	 */
	public function with($key, $value = null)
	{
		if (is_array($key)) {
			$this->data = array_merge($this->data, $key);
		} else {
			$this->data[$key] = $value;
		}

		return $this;
	}

	/**
	 * Get the view factory instance.
	 *
	 * @return \Illuminate\View\Factory
	 */
	public function getFactory()
	{
		return $this->factory;
	}

	/**
	 * Get the name of the view.
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->getName();
	}

	/**
	 * Get the name of the view.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->view;
	}

	/**
	 * Get the array of view data.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Get the path to the view file.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Set the path to the view.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}

	/**
	 * Determine if a piece of data is bound.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->data);
	}

	/**
	 * Get a piece of bound data to the view.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->data[$key];
	}

	/**
	 * Set a piece of data on the view.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->with($key, $value);
	}

	/**
	 * Unset a piece of data from the view.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->data[$key]);
	}

	/**
	 * Get a piece of data from the view.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function &__get($key)
	{
		return $this->data[$key];
	}

	/**
	 * Set a piece of data on the view.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->with($key, $value);
	}

	/**
	 * Check if a piece of data is bound to the view.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($this->data[$key]);
	}

	/**
	 * Remove a piece of bound data from the view.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function __unset($key)
	{
		unset($this->data[$key]);
	}

	/**
	 * Dynamically bind parameters to the view.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return \Illuminate\View\View
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $parameters)
	{
		if (Str::startsWith($method, 'with')) {
			return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
		}

		throw new BadMethodCallException("Method [$method] does not exist on view.");
	}

	/**
	 * Get the string contents of the view.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}
}
