<?php
namespace Lead\Sql\Dialect\Statement;

use Lead\Sql\Dialect\SqlException;

/**
 * `TRUNCATE` statement.
 */
class Truncate extends \Lead\Sql\Dialect\Statement
{
    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'table'      => ''
    ];

    /**
     * Sets the table name to create.
     *
     * @param  string $table The table name.
     * @return object        Returns `$this`.
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

        return 'TRUNCATE' . $this->_buildClause('TABLE', $this->dialect()->names($this->_parts['table']));
    }

}
