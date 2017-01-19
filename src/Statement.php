<?php
namespace Lead\Sql\Dialect;

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
    protected $_parts = [];

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
     * Builds a SQL chunk.
     *
     * @param  string $sql The SQL string.
     * @return string      The SQL chunk.
     */
    protected function _buildChunk($sql)
    {
        return $sql ? " {$sql}" : '';
    }
}
