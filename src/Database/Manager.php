<?php

namespace Enlighten\Database;

use Illuminate\Database\Capsule\Manager as BaseManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Container;
use Illuminate\Support\Facades\Facade;

class Manager extends BaseManager
{
    /**
     * Create a new database capsule manager.
     *
     * @param  \Illuminate\Container\Container|null  $container
     * @return void
     */
    public function __construct(Container $container = null)
    {
        parent::__construct(Facade::getFacadeApplication());
    }

    /**
     * Build the database manager instance.
     *
     * @return void
     */
    protected function setupManager()
    {
        parent::setupManager();

        $this->setAsGlobal();

        if (!$this->getEventDispatcher()) {
            $this->setEventDispatcher(new Dispatcher);
        }

        $this->bootEloquent();

        $this->manager->extend('wpdb', function($config, $name){
            return new Connection((new Connector)->connect($config, $name));
        });

        $this->addConnection(['driver' => 'wpdb'], 'default');
    }

    /**
     * Get a global instance of the capsule manager.
     *
     * @param \Illuminate\Support\Container|null $container
     * @return \Enlighten\Database\Eloquent\Manager
     */
    public static function getInstance()
    {
        if (static::$instance) {
            return static::$instance;
        }

        return new self;
    }
}
