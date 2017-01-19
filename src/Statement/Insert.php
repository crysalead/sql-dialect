<?php
namespace Lead\Sql\Dialect\Statement;

use Lead\Sql\Dialect\SqlException;
use Lead\Sql\Dialect\Statement\Behavior\HasFlags;

/**
 * INSERT statement.
 */
class Insert extends \Lead\Sql\Dialect\Statement
{
    use HasFlags;

    /**
     * The schema.
     *
     * @var mixed
     */
    protected $_schema = null;

    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'flags'     => [],
        'into'      => '',
        'values'    => [],
        'returning' => []
    ];

    /**
     * Constructor
     *
     * @param array $config The config array. The option is:
     *                       - 'schema' object the Schema instance to use.
     */
    public function __construct($config = [])
    {
        $defaults = ['schema' => null];
        $config += $defaults;
        parent::__construct($config);
        $this->_schema = $config['schema'];
    }

    /**
     * Sets the `INTO` clause value.
     *
     * @param  string $into The table name.
     * @return object       Returns `$this`.
     */
    public function into($into)
    {
        $this->_parts['into'] = $into;
        return $this;
    }

    /**
     * Sets the `INSERT` values.
     *
     * @param  string|array $values   The record values to insert.
     * @param  callable     $callable The type detector callable.
     * @return object                 Returns `$this`.
     */
    public function values($values)
    {
        $this->_parts['values'][] = $values;
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
        if (!$this->_parts['into']) {
            throw new SqlException("Invalid `INSERT` statement, missing `INTO` clause.");
        }

        $fields = count($this->_parts['values']) ? array_keys($this->_parts['values'][0]) : [];

        return 'INSERT' .
            $this->_buildFlags($this->_parts['flags']) .
            $this->_buildClause('INTO', $this->dialect()->name($this->_parts['into'], true)) .
            $this->_buildChunk('(' . $this->dialect()->names($fields, true) . ')', false) .
            $this->_buildValues() .
            $this->_buildClause('RETURNING', $this->dialect()->names($this->_parts['returning'], false, ''));
    }

    /**
     * Build `VALUES` clause.
     *
     * @return string Returns the `VALUES` clause.
     */
    protected function _buildValues()
    {
        $states =  $this->_schema ? ['schema' => $this->_schema] : [];
        $parts = [];
        foreach ($this->_parts['values'] as $values) {
            $data = [];
            foreach ($values as $key => $value) {
                $states['name'] = $key;
                $data[] = $this->dialect()->value($value, $states);
            }
            $parts[] = '(' . join(', ', $data) . ')';
        }
        return ' VALUES ' . join(', ',$parts);
    }
}
