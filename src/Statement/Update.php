<?php
namespace Lead\Sql\Statement;

use Lead\Sql\SqlException;
use Lead\Sql\Statement\Behavior\HasFlags;
use Lead\Sql\Statement\Behavior\HasWhere;
use Lead\Sql\Statement\Behavior\HasOrder;
use Lead\Sql\Statement\Behavior\HasLimit;

/**
 * `UPDATE` statement.
 */
class Update extends \Lead\Sql\Statement
{
    use HasFlags, HasWhere, HasOrder, HasLimit;

    /**
     * The type detector callable.
     *
     * @var callable
     */
    protected $_type = null;

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
     * @param  string|array $values   The record values to insert.
     * @param  callable     $callable The type detector callable.
     * @return object                 Returns `$this`.
     */
    public function values($values, $callable = null)
    {
        $this->_parts['values'] = $values;
        $this->_type = $callable;
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
        $states =  $this->_type ? ['type' => $this->_type] : [];
        foreach ($this->_parts['values'] as $key => $value) {
            $states['name'] = $key;
            $values[] = $this->dialect()->name($key) . ' = ' . $this->dialect()->value($value, $states);
        }
        return $values ? ' SET ' . join(', ', $values) : '';
    }
}
