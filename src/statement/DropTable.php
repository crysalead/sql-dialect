<?php
namespace sql\statement;

use sql\SqlException;

/**
 * `DROP TABLE` statement.
 */
class DropTable extends \sql\Statement
{
    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'ifExists' => false,
        'table'    => [],
        'cascade'  => false,
        'restrict' => false
    ];

    /**
     * Sets the requirements on the table existence.
     *
     * @param  boolean $ifExists If `false` the table must exists, use `true` for a soft drop.
     * @return object          Returns `$this`.
     */
    public function ifExists($ifExists = true)
    {
        $this->_parts['ifExists'] = $ifExists;
        return $this;
    }

    /**
     * Set the table name to create.
     *
     * @param  string $table The table name.
     * @return object        Returns `$this`.
     */
    public function table($table)
    {
        $tables = is_array($table) ? $table : func_get_args();
        $this->_parts['table'] = $tables;
        return $this;
    }

    /**
     * Sets cascading value.
     *
     * @param  boolean $cascade If `true` the related views or objects will be removed.
     * @return object           Returns `$this`.
     */
    public function cascade($cascade = true)
    {
        $this->_parts['cascade'] = $cascade;
        return $this;
    }

    /**
     * Sets restricting value.
     *
     * @param  boolean $restrict If `true` the table won't be removed if the related views or objects exists.
     * @return object            Returns `$this`.
     */
    public function restrict($restrict = true)
    {
        $this->_parts['restrict'] = $restrict;
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
            throw new SqlException("Invalid `DROP TABLE` statement, missing `TABLE` clause.");
        }

        return 'DROP TABLE' .
            $this->_buildFlag('IF EXISTS', $this->_parts['ifExists']) .
            $this->_buildChunk($this->dialect()->names($this->_parts['table'])) .
            $this->_buildFlag('CASCADE', $this->_parts['cascade']) .
            $this->_buildFlag('RESTRICT', $this->_parts['restrict']);
    }
}
