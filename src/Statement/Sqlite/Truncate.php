<?php
namespace Lead\Sql\Dialect\Statement\Sqlite;

use Lead\Sql\Dialect\SqlException;

/**
 * `TRUNCATE` compatibility statement.
 */
class Truncate extends \Lead\Sql\Dialect\Statement
{
    /**
     * The schema.
     *
     * @var mixed
     */
    protected $_schema = null;

    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'table'     => ''
    ];

    /**
     * Constructor
     *
     * @param array $config The config array. The option is:
     *                       - 'schema' object the Schema instance to use.
     */
    public function __construct($config = [])
    {
        $defaults = ['schema' => null];
        $config += $defaults;
        parent::__construct($config);
        $this->_schema = $config['schema'];
    }

    /**
     * Sets the table name to create.
     *
     * @param  string $table The table name.
     * @return object       Returns `$this`.
     */
    public function table($table)
    {
        $this->_parts['table'] = $table;
        return $this;
    }

    /**
     * Render the SQL statement
     *
     * @return string The generated SQL string.
     * @throws SqlException
     */
    public function toString()
    {
        if (!$this->_parts['table']) {
            throw new SqlException("Invalid `TRUNCATE` statement, missing `TABLE` clause.");
        }

        return 'DELETE' . $this->_buildClause('FROM', $this->dialect()->names($this->_parts['table'])) . ';' .
            'DELETE' . $this->_buildClause('FROM', $this->dialect()->names('SQLITE_SEQUENCE')) .
                ' WHERE name=' . $this->dialect()->names($this->_parts['table']);
    }

}
