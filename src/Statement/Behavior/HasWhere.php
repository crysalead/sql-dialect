<?php
namespace Lead\Sql\Dialect\Statement\Behavior;

trait HasWhere
{
    /**
     * Adds some where conditions to the query.
     *
     * @param  string|array $conditions The conditions for this query.
     * @return object                   Returns `$this`.
     */
    public function where($conditions)
    {
        if ($conditions = is_array($conditions) && func_num_args() === 1 ? $conditions : func_get_args()) {
            $this->_parts['where'][] = $conditions;
        }
        return $this;
    }
}
