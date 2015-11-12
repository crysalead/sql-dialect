<?php
namespace sql\statement;

use sql\SqlException;

/**
 * `SELECT` statement.
 */
class Select extends \sql\Statement
{
    /**
     * Subquery alias
     */
    protected $_alias = null;

    /**
     * The SQL parts.
     *
     * @var string
     */
    protected $_parts = [
        'flags'     => [],
        'fields'    => [],
        'from'      => [],
        'joins'     => [],
        'where'     => [],
        'group'     => [],
        'having'    => [],
        'order'     => [],
        'limit'     => '',
        'forUpdate' => false
    ];

    /**
     * Sets `DISTINCT` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function distinct($enable = true)
    {
        $this->setFlag('DISTINCT', $enable);
        return $this;
    }

    /**
     * Adds some fields to the query.
     *
     * @param  string|array $fields The fields.
     * @return string               Formatted fields list.
     */
    public function fields($fields)
    {
        $fields = is_array($fields) && func_num_args() === 1 ? $fields : func_get_args();
        $this->_parts['fields'] = array_merge($this->_parts['fields'], $fields);
        return $this;
    }

    /**
     * Adds some tables in the from statement
     *
     * @param  string|array $sources The source tables.
     * @return string                Formatted source table list.
     */
    public function from($sources)
    {
        if (!$sources) {
            throw new SqlException("A `FROM` clause requires a non empty table.");
        }
        $sources = is_array($sources) ? $sources : func_get_args();
        $this->_parts['from'] += array_merge($this->_parts['from'], $sources);
        return $this;
    }

    /**
     * Adds a join to the query.
     *
     * @param  string|array $join A join definition.
     * @return string             Formatted `JOIN` clause.
     */
    public function join($join = null, $on = [], $type = 'LEFT')
    {
        if (!$join) {
            return $this;
        }
        $this->_parts['joins'][] = compact('join', 'on', 'type');
        return $this;
    }

    /**
     * Adds some where conditions to the query
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
     * Adds some group by fields to the query
     *
     * @param  string|array $fields The fields.
     * @return object                   Returns `$this`.
     */
    public function group($fields)
    {
        if (!$fields) {
            return $this;
        }
        if ($fields = is_array($fields) ? $fields : func_get_args()) {
            $this->_parts['group'] = array_merge($this->_parts['group'], array_fill_keys($fields, true));
        }
        return $this;
    }

    /**
     * Adds some having conditions to the query.
     *
     * @param  string|array $conditions The havings for this query.
     * @return object                   Returns `$this`.
     */
    public function having($conditions)
    {
        if ($conditions = is_array($conditions) && func_num_args() === 1 ? $conditions : func_get_args()) {
            $this->_parts['having'][] = $conditions;
        }
        return $this;
    }

    /**
     * Adds some order by fields to the query
     *
     * @param  string|array $fields The fields.
     * @return object                   Returns `$this`.
     */
    public function order($fields)
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
     * Adds a limit statement to the query
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
     * Sets `FOR UPDATE` mode.
     *
     * @param  boolean $forUpdate The `FOR UPDATE` value.
     * @return object             Returns `$this`.
     */
    public function forUpdate($forUpdate = true)
    {
        $this->_parts['forUpdate'] = $forUpdate;
        return $this;
    }

    /**
     * If called with a valid alias, the generated select statement
     * will be generated as a subquery.
     *
     * @param  string $alias The alias to use for a subquery.
     * @return string        Returns the alias or `$this` on set.
     */
    public function alias($alias = null)
    {
        if (!func_num_args()) {
            return $this->_alias;
        }
        $this->_alias = $alias;
        return $this;
    }

    /**
     * Render the SQL statement
     *
     * @return string The generated SQL string.
     */
    public function toString()
    {
        $fields = $this->dialect()->names($this->_parts['fields']);

        $sql = 'SELECT' .
            $this->_buildFlags($this->_parts['flags']) .
            $this->_buildChunk($fields ?: '*') .
            $this->_buildClause('FROM', $this->dialect()->names($this->_parts['from'])) .
            $this->_buildJoins() .
            $this->_buildClause('WHERE', $this->dialect()->conditions($this->_parts['where'])) .
            $this->_buildClause('GROUP BY', $this->_group($this->_parts['group'])) .
            $this->_buildClause('HAVING', $this->dialect()->conditions($this->_parts['having'])) .
            $this->_buildOrder($this->_parts['order']) .
            $this->_buildClause('LIMIT', $this->_parts['limit']) .
            $this->_buildFlag('FOR UPDATE', $this->_parts['forUpdate']);

        return $this->_alias ? "({$sql}) AS " . $this->dialect()->name($this->_alias) : $sql;
    }

    /**
     * Build the `GROUP BY` clause.
     *
     * @return string The `GROUP BY` clause.
     */
    protected function _group()
    {
        $result = [];
        foreach ($fields as $name => $value) {
            $result[] = $this->dialect()->name($name);
        }
        return $fields = join(', ', $result);
    }

    /**
     * Build the `JOIN` clause.
     *
     * @return string The `JOIN` clause.
     */
    protected function _buildJoins()
    {
        $joins = [];
        foreach ($this->_parts['joins'] as $value) {
            $table = $value['join'];
            $on = $value['on'];
            $type = $value['type'];
            $join = [strtoupper($type), 'JOIN'];
            $join[] = $this->dialect()->name($table, true);

            if ($on) {
                $join[] = 'ON';
                $join[] = $this->dialect()->conditions($on);
            }

            $joins[] = join(' ', $join);
        }
        return $joins ? ' ' . join(' ', $joins) : '';
    }

}
