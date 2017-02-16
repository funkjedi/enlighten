<?php

namespace Enlighten\Database\Facades;

use Illuminate\Support\Facades\Facade;
use Enlighten\Database\Manager;

class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return \Enlighten\Database\Eloquent\Manager|string
     */
    protected static function getFacadeAccessor()
    {
        return Manager::getInstance();
    }
}