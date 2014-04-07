<?php
namespace sql\statement\postgresql;

/**
 * `INSERT` statement.
 */
class Insert extends \sql\statement\Insert
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
