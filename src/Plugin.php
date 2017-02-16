<?php

namespace Enlighten;

class Plugin
{
	/**
	 * @var \Enlighten\Application
	 */
	protected $app;

	/**
	 * @var string
	 */
	protected $pluginFile;

	/**
	 * Create an instance.
	 */
	public function __construct($pluginFile)
	{
		$this->pluginFile = $pluginFile;

		$this->app = Application::getInstance();

		$this->app->setBasePath(dirname($pluginFile));

		$this->app->instance('plugin', $this);
	}

	/**
	 * Get the filesystem directory path relative to the plugin.
	 *
	 * @param string
	 * @return string
	 */
	public function path($path = '')
	{
		return plugin_dir_path($this->pluginFile) . ltrim($path, DIRECTORY_SEPARATOR);
	}

	/**
	 * Get the URL relative to the plugin.
	 *
	 * @param string
	 * @return string
	 */
	public function url($path = '')
	{
		return plugin_dir_url($this->pluginFile) . ltrim($path, DIRECTORY_SEPARATOR);
	}

	/**
	 * @return \Enlighten\Application
	 */
	public function getApplication()
	{
		return $this->app;
	}
}
