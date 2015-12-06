<?php
namespace Lead\Sql;

use Lead\Sql\SqlException;

/**
 * Statement
 */
class Statement
{
	/**
     * Pointer to the dialect adapter.
     *
     * @var object
     */
    protected $_dialect = null;

    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'flags' => ''
    ];

    /**
     * Constructor
     *
     * @param array $config The config array. The option is:
     *                       - 'dialect' `object` a dialect adapter.
     */
    public function __construct($config = [])
    {
        $defaults = ['dialect' => null];
        $config += $defaults;
        $this->_dialect = $config['dialect'];
    }

    /**
     * Gets/sets the dialect instance
     *
     * @param  object $dialect The dialect instance to set or none the get the setted one.
     * @return object          The dialect instance or `$this` on set.
     */
    public function dialect($dialect = null)
    {
        if ($dialect !== null) {
            $this->_dialect = $dialect;
            return $this;
        }
        if (!$this->_dialect) {
            throw new SqlException('Missing SQL dialect adapter.');
        }
        return $this->_dialect;
    }

    /**
     * Gets/sets data to the statement.
     *
     * @param  string $name  The name of the value to set/get.
     * @param  mixed  $value The value to set.
     * @return mixed         The setted value.
     */
    public function data($name, $value = null)
    {
        if (func_num_args() === 2) {
            return $this->_parts[$name] = $value;
        }
        return isset($this->_parts[$name]) ? $this->_parts[$name] : null;
    }

    /**
     * Sets a flag.
     *
     * @param  string  $name   The name of the flag to set.
     * @param  boolean $enable The boolean value to set.
     * @return boolean         The flag value.
     */
    public function setFlag($flag, $enable = true)
    {
        return $this->_parts['flags'][$flag] = !!$enable;
    }

    /**
     * Gets a flag.
     *
     * @param  string  $name The name of the flag to get.
     * @return boolean       The flag value.
     */
    public function getFlag($flag)
    {
        return isset($this->_parts['flags'][$flag]) ? $this->_parts['flags'][$flag] : null;
    }

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
     * Adds a limit statement to the query.
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
     * Throws an error for invalid clauses.
     *
     * @param string $name   The name of the matcher.
     * @param array  $params The parameters to pass to the matcher.
     */
    public function __call($name, $params)
    {
        throw new SqlException("Invalid clause `{$name}` for `" . get_called_class() . "`.");
    }

    /**
     * Builds a string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Builds a clause.
     *
     * @param  string $clause     The clause name.
     * @param  string $expression The expression.
     * @return string             The clause.
     */
    protected function _buildClause($clause, $expression)
    {
        return $expression ? " {$clause} {$expression}": '';
    }

    /**
     * Builds Flags.
     *
     * @param  array  $flags  The flags map.
     * @return string         The formatted flags.
     */
    protected function _buildFlags($flags)
    {
        $flags = array_filter($flags);
        return $flags ? ' ' . join(' ', array_keys($flags)) : '';
    }

    /**
     * Builds a Flag chunk.
     *
     * @param  string  $flag  The flag name.
     * @param  boolean $value The value.
     * @return string         The SQL flag.
     */
    protected function _buildFlag($flag, $value)
    {
        return $value ? " {$flag}": '';
    }

    /**
     * Builds a SQL chunk.
     *
     * @param  string $sql The SQL string.
     * @return string      The SQL chunk.
     */
    protected function _buildChunk($sql)
    {
        return $sql ? " {$sql}" : '';
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
