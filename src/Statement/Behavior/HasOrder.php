<?php
namespace Lead\Sql\Statement\Behavior;

trait HasOrder
{
    /**
     * Adds some order by fields to the query.
     *
     * @param  string|array $fields The fields.
     * @return object               Returns `$this`.
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
     * Order formatter helper method
     *
     * @param  string|array $fields The fields.
     * @return string       Formatted fields.
     */
    protected function _order($fields)
    {
        $direction = 'ASC';

        $result = [];
        foreach ($fields as $field => $value) {
            if (!is_int($field)) {
                $result[$field] = $value;
                continue;
            }
            if (preg_match('/^(.*?)\s+((?:a|de)sc)$/i', $value, $match)) {
                $value = $match[1];
                $dir = $match[2];
            } else {
                $dir = $direction;
            }
            $result[$value] = $dir;
        }
        return $result;
    }

    /**
     * Builds the `ORDER BY` clause.
     *
     * @return string The `ORDER BY` clause.
     */
    protected function _buildOrder()
    {
        $result = [];
        foreach ($this->_parts['order'] as $column => $dir) {
            $column = $this->dialect()->name($column);
            $result[] = "{$column} {$dir}";
        }
        return $this->_buildClause('ORDER BY', join(', ', $result));
    }
}
