<?php

namespace Enlighten\Database;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
        $this->app['db']->extend('wpdb', function($config, $name){
            return new Connection((new Connector)->connect($config, $name));
        });
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
}
