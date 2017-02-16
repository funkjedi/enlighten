<?php

namespace Enlighten\Database\Eloquent;

use Enlighten\Database\Connection;
use Enlighten\Database\Manager;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent
{
    /**
     * Indicates whether the WP table prefix should be used for the model.
     *
     * @var boolean
     */
    protected $tablePrefix = true;

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) return $this->table;

        $table = parent::getTable();

        if ($this->tablePrefix && $this->getConnection() instanceof Connection) {
            $table = $this->getConnection()->getWpdb()->prefix . $table;
        }

        return $table;
    }

    /**
     * Resolve a connection instance.
     *
     * @param  string  $connection
     * @return \Illuminate\Database\Connection
     */
    public static function resolveConnection($connection = null)
    {
        // If a resolver hasn't been configured then use an
        // instance of the Manager to resolve connection
        if (static::$resolver === null) {
            Manager::getInstance();
        }

        return parent::resolveConnection($connection);
    }
}