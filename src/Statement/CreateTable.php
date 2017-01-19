<?php
namespace Lead\Sql\Dialect\Statement;

use Lead\Sql\Dialect\SqlException;

/**
 * `CREATE TABLE` statement.
 */
class CreateTable extends \Lead\Sql\Dialect\Statement
{
    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'ifNotExists' => false,
        'table'       => '',
        'columns'     => [],
        'constraints' => [],
        'meta'        => []
    ];

    /**
     * Sets the requirements on the table existence.
     *
     * @param  boolean $ifNotExists If `false` the table must not exists, use `true` for a soft create.
     * @return object               Returns `$this`.
     */
    public function ifNotExists($ifNotExists = true)
    {
        $this->_parts['ifNotExists'] = $ifNotExists;
        return $this;
    }

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
     * Adds some columns to the query.
     *
     * @param  array $columns An array of fields description.
     * @return object         Returns `$this`.
     */
    public function columns($columns)
    {
        $this->_parts['columns'] += $columns;
        return $this;
    }

    /**
     * Sets some table meta to the query.
     *
     * @param  array  $meta An array of meta for the table.
     * @return object       Returns `$this`.
     */
    public function meta($meta)
    {
        $this->_parts['meta'] = $meta;
        return $this;
    }

    /**
     * Sets constraints to the query.
     *
     * @param  array  $constraints The constraints array definition for columns.
     * @return object              Returns `$this`.
     */
    public function constraints($constraints)
    {
        $this->_parts['constraints'] = $constraints;
        return $this;
    }

    /**
     * Adds a constraint to the query.
     *
     * @param  array  $constraint An constraint array definition for columns.
     * @return object             Returns `$this`.
     */
    public function constraint($constraint)
    {
        $this->_parts['constraints'][] =  $constraint;
        return $this;
    }

    /**
     * Returns the normalized type of a column.
     *
     * @param  string $name The name of the column.
     * @return string       Returns the normalized column type.
     */
    public function type($name)
    {
        if (isset($this->_parts['columns'][$name]['type'])) {
            return $this->_parts['columns'][$name]['type'];
        }
        return 'string';
    }

    /**
     * Render the SQL statement.
     *
     * @return string The generated SQL string.
     */
    public function toString()
    {
        if (!$this->_parts['table']) {
            throw new SqlException("Invalid `CREATE TABLE` statement missing table name.");
        }

        if (!$this->_parts['columns']) {
            throw new SqlException("Invalid `CREATE TABLE` statement missing columns.");
        }

        return 'CREATE TABLE' .
            $this->_buildFlag('IF NOT EXISTS', $this->_parts['ifNotExists']) .
            $this->_buildChunk($this->dialect()->name($this->_parts['table'])) .
            $this->_buildDefinition($this->_parts['columns'], $this->_parts['constraints']) .
            $this->_buildChunk($this->dialect()->meta('table', $this->_parts['meta']));
    }

    /**
     * Helper for building columns definition.
     *
     * @param  array  $columns     The columns.
     * @param  array  $constraints The columns constraints.
     * @return string              The SQL columns definition list.
     */
    protected function _buildDefinition($columns, $constraints)
    {
        foreach ($columns as $name => $field) {
            $field['name'] = $name;
            $field = $this->dialect()->field($field);

            if (!empty($field['serial'])) {
                $primary = $name;
            }
            $result[] = $this->dialect()->column($field);
        }

        foreach ($constraints as $constraint) {
            if (!isset($constraint['type'])) {
                throw new SqlException("Missing contraint type.");
            }
            $type = $constraint['type'];
            if ($meta = $this->dialect()->constraint($type, $constraint, ['schemas' => ['' => $this]])) {
                $result[] = $meta;
            }
            if ($type === 'primary') {
                $primary = null;
            }
        }
        if (isset($primary)) {
            $result[] = $this->dialect()->constraint('primary', ['column' => $primary]);
        }

        return ' (' . join(', ', array_filter($result)) . ')';
    }

}
