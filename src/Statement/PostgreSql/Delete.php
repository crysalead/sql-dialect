<?php
namespace Lead\Sql\Dialect\Statement\PostgreSql;

/**
 * `DELETE` statement.
 */
class Delete extends \Lead\Sql\Dialect\Statement\Delete
{
    /**
     * Sets some fields to the `RETURNING` clause.
     *
     * @param  string|array $fields The fields.
     * @return string               Formatted fields list.
     */
    public function returning($fields)
    {
        $this->_parts['returning'] = array_merge($this->_parts['returning'], is_array($fields) ? $fields : func_get_args());
        return $this;
    }
}
