<?php

namespace Enlighten\Database;

use Illuminate\Database\Connectors\ConnectorInterface;
use wpdb;

class Connector implements ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @param  string|null  $name
     * @return \wpdb
     */
    public function connect(array $config, $name = null)
    {
        global $wpdb;

        if ($name === 'default') {
            return $wpdb;
        }

        return $this->createConnection($config);
    }

    /**
     * Create a new wpdb connection.
     *
     * @param  array   $config
     * @return \wpdb
     */
    public function createConnection(array $config)
    {
        $connection = new wpdb(
            array_get($config, 'username'),
            array_get($config, 'password'),
            array_get($config, 'database'),
            array_get($config, 'host')
        );

        $collation = $config['collation'];

        // Next we will set the "names" and "collation" on the clients connections so
        // a correct character set will be used by this client. The collation also
        // is set on the server but needs to be set here on this client objects.
        $charset = $config['charset'];

        $names = "set names '$charset'".
            ( ! is_null($collation) ? " collate '$collation'" : '');

        $connection->query($names);

        // Next, we will check to see if a timezone has been specified in this config
        // and if it has we will issue a statement to modify the timezone with the
        // database. Setting this DB timezone is an optional configuration item.
        if (isset($config['timezone']))
        {
            $connection->query('set time_zone="'.$config['timezone'].'"');
        }

        // If the "strict" option has been configured for the connection we'll enable
        // strict mode on all of these tables. This enforces some extra rules when
        // using the MySQL database system and is a quicker way to enforce them.
        if (isset($config['strict']) && $config['strict'])
        {
            $connection->query("set session sql_mode='STRICT_ALL_TABLES'");
        }

        return $connection;
    }
}
