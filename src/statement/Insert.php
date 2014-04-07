<?php
namespace sql\statement;

use sql\SqlException;

/**
 * INSERT statement.
 */
class Insert extends \sql\Statement
{
    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'flags'     => [],
        'into'      => '',
        'values'    => [],
        'returning' => []
    ];

    /**
     * Sets the `INTO` clause value.
     *
     * @param  string|array $into The table name.
     * @return object              Returns `$this`.
     */
    public function into($into)
    {
        $this->_parts['into'] = $into;
        return $this;
    }

    /**
     * Sets the `INSERT` values.
     *
     * @param  string|array $values The record values to insert.
     * @return object               Returns `$this`.
     */
    public function values($values)
    {
        $this->_parts['values'] = $values;
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
        if (!$this->_parts['into']) {
            throw new SqlException("Invalid `INSERT` statement, missing `INTO` clause.");
        }

        $fields = array_keys($this->_parts['values']);
        $values = array_values($this->_parts['values']);

        return 'INSERT' .
            $this->_buildFlags($this->_parts['flags']) .
            $this->_buildClause('INTO', $this->dialect()->name($this->_parts['into'], true)) .
            $this->_buildChunk('(' . $this->dialect()->names($fields, true) . ')', false) .
            $this->_buildChunk('VALUES (' . join(', ', array_map([$this->dialect(), 'value'], $values)) . ')') .
            $this->_buildClause('RETURNING', $this->dialect()->names($this->_parts['returning'], false, ''));
    }

}
