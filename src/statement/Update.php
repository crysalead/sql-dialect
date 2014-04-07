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
        'table'     => [],
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
     * Adds some where conditions to the query
     *
     * @param  string|array $conditions The conditions for this query.
     * @return object                   Returns `$this`.
     */
    public function where($conditions)
    {
        if ($conditions = $this->dialect()->conditions($conditions)) {
            $this->_parts['where'][] = $conditions;
        }
        return $this;
    }

    /**
     * Adds some order by fields to the query
     *
     * @param  string|array $fields The fields.
     * @return object                   Returns `$this`.
     */
    public function order($fields = null)
    {
        if (!$fields) {
            return $this;
        }
        if ($fields = is_array($fields) ? $fields : func_get_args()) {
            $this->_parts['order'] = array_merge($this->_parts['order'], $this->_order($fields));
        }
        return $this;
    }

    /**
     * Adds a limit statement to the query
     *
     * @param  integer $limit  The limit value.
     * @param  integer $offset The offset value.
     * @return object          Returns `$this`.
     */
    public function limit($limit = 0, $offset = 0)
    {
        if (!$limit) {
            return $this;
        }
        if ($offset) {
            $limit .= " OFFSET {$offset}";
        }
        $this->_parts['limit'] = $limit;
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
            throw new SqlException("Invalid `UPDATE` statement, missing `TABLE` clause.");
        }

        if (!$this->_parts['values']) {
            throw new SqlException("Invalid `UPDATE` statement, missing `VALUES` clause.");
        }

        return 'UPDATE' .
            $this->_buildFlags($this->_parts['flags']) .
            $this->_buildChunk($this->dialect()->names($this->_parts['table'])) .
            $this->_buildValues() .
            $this->_buildClause('WHERE', join(' AND ', $this->_parts['where'])) .
            $this->_buildOrder($this->_parts['order']) .
            $this->_buildClause('LIMIT', $this->_parts['limit']) .
            $this->_buildClause('RETURNING', $this->dialect()->names($this->_parts['returning']));
    }

    protected function _buildValues()
    {
        $values = [];
        foreach ($this->_parts['values'] as $key => $value) {
            $values[] = $this->dialect()->name($key) . ' = ' . $this->dialect()->value($value);
        }
        return $values ? ' SET ' . join(', ', $values) : '';
    }
}
