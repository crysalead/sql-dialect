<?php
namespace sql\statement;

use sql\SqlException;

/**
 * `UPDATE` statement.
 */
class Update extends \sql\Statement
{
    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'flags'     => [],
        'table'     => '',
        'values'    => [],
        'where'     => [],
        'order'     => [],
        'limit'     => '',
        'returning' => []
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
     * Sets the `UPDATE` values.
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
     */
    public function toString()
    {
        if (!$this->_parts['table']) {
            throw new SqlException("Invalid `UPDATE` statement, missing `TABLE` clause.");
        }

        if (!$this->_parts['values']) {
            throw new SqlException("Invalid `UPDATE` statement, missing `VALUES` clause.");
        }

        return 'UPDATE' .
            $this->_buildFlags($this->_parts['flags']) .
            $this->_buildChunk($this->dialect()->names($this->_parts['table'])) .
            $this->_buildSet() .
            $this->_buildClause('WHERE', $this->dialect()->conditions($this->_parts['where'])) .
            $this->_buildOrder() .
            $this->_buildClause('LIMIT', $this->_parts['limit']) .
            $this->_buildClause('RETURNING', $this->dialect()->names($this->_parts['returning']));
    }

    /**
     * Build `SET` clause.
     *
     * @return string Returns the `SET` clause.
     */
    protected function _buildSet()
    {
        $values = [];
        foreach ($this->_parts['values'] as $key => $value) {
            $values[] = $this->dialect()->name($key) . ' = ' . $this->dialect()->value($value);
        }
        return $values ? ' SET ' . join(', ', $values) : '';
    }
}
