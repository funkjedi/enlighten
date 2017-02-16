<?php

namespace Enlighten\Database;

use Closure;
use DateTime;
use Exception;
use Illuminate\Database\Grammar;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\QueryException;
use PDO;
use RuntimeException;
use wpdb;

class Connection extends MySqlConnection
{
    /**
     * The active wpdb connection.
     *
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * The default fetch mode of the connection.
     *
     * @var int
     */
    protected $fetchMode = ARRAY_A;

    /**
     * Create a new database connection instance.
     *
     * @param  \wpdb    $wpdb
     * @param  array    $config
     * @return void
     */
    public function __construct(wpdb $wpdb, $tablePrefix = '', array $config = array())
    {
        $this->wpdb = $wpdb;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;

        // We need to initialize a query grammar and the query post processors
        // which are both very important parts of the database abstractions
        // so we initialize these to their default values while starting.
        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Enlighten\Database\Eloquent\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new Processor;
    }

    /**
     * Run a select statement against the database.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function select($query, $bindings = array(), $useReadPdo = true)
    {
        return $this->run($query, $bindings, function($me, $query, $bindings) use ($useReadPdo)
        {
            if ($me->pretending()) return array();

            // For select statements, we'll simply execute the query and return an array
            // of the database result set. Each element in the array will be a single
            // row from the database table, and will either be an array or objects.
            $query = $me->emulatePrepare($query, $bindings);

            $results = $this->wpdb->get_results($query, $me->getFetchMode());

            if ($this->wpdb->last_error) {
                throw new Exception($this->wpdb->last_error);
            }

            return $results;
        });
    }

    /**
     * Get the PDO connection to use for a select query.
     *
     * @param  bool  $useReadPdo
     * @return \PDO
     */
    protected function getPdoForSelect($useReadPdo = true)
    {
        throw new RuntimeException("Connection does not support the PDO driver.");
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function statement($query, $bindings = array())
    {
        return $this->run($query, $bindings, function($me, $query, $bindings)
        {
            if ($me->pretending()) return true;

            $query = $me->emulatePrepare($query, $bindings);

            return (bool) $me->query($query);
        });
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = array())
    {
        return $this->run($query, $bindings, function($me, $query, $bindings)
        {
            if ($me->pretending()) return 0;

            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use wpdb to fetch the affected.
            $query = $me->emulatePrepare($query, $bindings);

            $me->query($query);

            return $this->wpdb->rows_affected;
        });
    }

    /**
     * Run a raw, unprepared query against the wpdb connection.
     *
     * @param  string  $query
     * @return bool
     */
    public function unprepared($query)
    {
        return $this->run($query, array(), function($me, $query)
        {
            if ($me->pretending()) return true;

            return (bool) $me->query($query);
        });
    }

    /**
     * Run a raw, unprepared query against the wpdb connection.
     *
     * @param  string  $query
     * @return mixed
     */
    protected function query($query)
    {
        $result = $this->wpdb->query($query);

        if ($result === false) {
            throw new Exception($this->wpdb->last_error);
        }

        return $result;
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        ++$this->transactions;

        if ($this->transactions == 1)
        {
            $this->unprepared('START TRANSACTION;');
        }

        $this->fireConnectionEvent('beganTransaction');
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactions == 1) $this->unprepared('COMMIT;');

        --$this->transactions;

        $this->fireConnectionEvent('committed');
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        if ($this->transactions == 1)
        {
            $this->transactions = 0;

            $this->unprepared('ROLLBACK;');
        }
        else
        {
            --$this->transactions;
        }

        $this->fireConnectionEvent('rollingBack');
    }

    /**
     * Run a SQL statement.
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the wpdb connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
        try
        {
            $result = $callback($this, $query, $bindings);
        }

        // If an exception occurs when attempting to run a query, we'll format the error
        // message to include the bindings with SQL, which will make this exception a
        // lot more helpful to the developer instead of just the database's errors.
        catch (Exception $e)
        {
            throw new QueryException(
                $query, $this->prepareBindings($bindings), $e
            );
        }

        return $result;
    }

    /**
     * Disconnect from the underlying PDO connection.
     *
     * @return void
     */
    public function disconnect()
    {
        throw new RuntimeException("Connection does not support disconnecting.");
    }

    /**
     * Reconnect to the database.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function reconnect()
    {
        $this->wpdb->check_connection();
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
     *
     * @return void
     */
    protected function reconnectIfMissingConnection()
    {
        $this->reconnect();
    }

    /**
     * Get the Doctrine DBAL driver.
     *
     * @return \Doctrine\DBAL\Driver\PDOMySql\Driver
     */
    protected function getDoctrineDriver()
    {
        throw new RuntimeException("Connection does not support the Doctrine DBAL driver.");
    }

    /**
     * Get the current wpdb connection.
     *
     * @return \wpdb
     */
    public function getWpdb()
    {
        return $this->wpdb;
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo()
    {
        // Required to maintain compatiblity with the Database Manager
    }

    /**
     * Get the current PDO connection used for reading.
     *
     * @return \PDO
     */
    public function getReadPdo()
    {
        // Required to maintain compatiblity with the Database Manager
    }

    /**
     * Set the PDO connection.
     *
     * @param  \PDO|null  $pdo
     * @return $this
     */
    public function setPdo($pdo)
    {
        // Required to maintain compatiblity with the Database Manager
    }

    /**
     * Set the PDO connection used for reading.
     *
     * @param  \PDO|null  $pdo
     * @return $this
     */
    public function setReadPdo($pdo)
    {
        // Required to maintain compatiblity with the Database Manager
    }

    /**
     * Set the reconnect instance on the connection.
     *
     * @param  callable  $reconnector
     * @return $this
     */
    public function setReconnector(callable $reconnector)
    {
        // Required to maintain compatiblity with the Database Manager
    }

    /**
     * Get the driver name.
     *
     * @return string
     */
    public function getDriverName()
    {
        return EZSQL_VERSION;
    }

    /**
     * Set the default fetch mode for the connection.
     *
     * @param  int  $fetchMode
     * @return int
     */
    public function setFetchMode($fetchMode)
    {
        switch ($fetchMode) {
            case ARRAY_A:          $fetchMode = ARRAY_A;  break;
            case ARRAY_N:          $fetchMode = ARRAY_N;  break;
            case OBJECT:           $fetchMode = OBJECT;   break;
            case OBJECT_K:         $fetchMode = OBJECT_K; break;
            case PDO::FETCH_ASSOC: $fetchMode = ARRAY_A;  break;
            case PDO::FETCH_NUM:   $fetchMode = ARRAY_N;  break;
            case PDO::FETCH_OBJ:   $fetchMode = OBJECT;   break;
            default:          $fetchMode = ARRAY_A;
        }

        $this->fetchMode = $fetchMode;
    }

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->wpdb->dbname;
    }

    /**
     * Set the name of the connected database.
     *
     * @param  string  $database
     * @return string
     */
    public function setDatabaseName($database)
    {
        throw new RuntimeException("Connection does not support changing databases.");
    }

    /**
     * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
     *
     * @link https://github.com/WordPress/WordPress/blob/master/wp-includes/wp-db.php#L1305
     *
     * @param $query
     * @param $bindings
     *
     * @return mixed
     */
    private function emulatePrepare($query, array $bindings = array(), $update = false)
    {
        // Replace percentage signs with a double percentage sign so
        // to ensure that vsprintf treats them as literal characters
        $query = str_replace('%', '%%', $query);

        // Replace question mark placeholders with the vsprintf %s placeholder
        // and in without replacing question marks that have been quoted with a backtick
        $query = preg_replace('#\?(?=(?:[^`]*`[^`]*`)*[^`]*\Z)#', '%s', $query);

        if (count($bindings) === 0) {
            return $query;
        }

        $query = str_replace("'%s'", '%s', $query); // in case someone mistakenly already singlequoted it
        $query = str_replace('"%s"', '%s', $query); // doublequote unquoting
        $query = preg_replace('|(?<!%)%f|', '%F', $query); // Force floats to be locale unaware

        $bindings = $this->prepareBindings($bindings);

        // This is where we differ from wpdb::prepare() instead wrapping the placeholders
        // with quotes we wrap the bindings with quotes. This is require to add support for NULLs.
        foreach ($bindings as &$binding) {
            if ($binding === null) {
                $binding = 'NULL';
            } else {
                $this->wpdb->escape_by_ref($binding);
                $binding = "'$binding'";
            }
        }

        return @vsprintf($query, $bindings);
    }
}
