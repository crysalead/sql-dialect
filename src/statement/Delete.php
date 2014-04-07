<?php
namespace sql\statement;

use sql\SqlException;

/**
 * `DELETE` statement.
 */
class Delete extends \sql\Statement
{
    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'flags'     => [],
        'from'      => [],
        'where'     => [],
        'order'     => [],
        'limit'     => '',
        'returning' => []
    ];

    /**
     * Sets the table name to create.
     *
     * @param  string $from The table name.
     * @return object       Returns `$this`.
     */
    public function from($from)
    {
        $this->_parts['from'] = $from;
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
        if (!$this->_parts['from']) {
            throw new SqlException("Invalid `DELETE` statement, missing `FROM` clause.");
        }

        return 'DELETE' .
            $this->_buildFlags($this->_parts['flags']) .
            $this->_buildClause('FROM', $this->dialect()->names($this->_parts['from'])) .
            $this->_buildClause('WHERE', join(' AND ', $this->_parts['where'])) .
            $this->_buildOrder($this->_parts['order']) .
            $this->_buildClause('LIMIT', $this->_parts['limit']) .
            $this->_buildClause('RETURNING', $this->dialect()->names($this->_parts['returning']));
    }

}
