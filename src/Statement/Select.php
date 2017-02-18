<?php
namespace Lead\Sql\Dialect\Statement;

use Lead\Sql\Dialect\SqlException;
use Lead\Sql\Dialect\Statement\Behavior\HasFlags;
use Lead\Sql\Dialect\Statement\Behavior\HasWhere;
use Lead\Sql\Dialect\Statement\Behavior\HasOrder;
use Lead\Sql\Dialect\Statement\Behavior\HasLimit;

/**
 * `SELECT` statement.
 */
class Select extends \Lead\Sql\Dialect\Statement
{
    use HasFlags, HasWhere, HasOrder, HasLimit;

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
     * Adds some group by fields to the query.
     *
     * @param  string|array $fields The fields.
     * @return object               Returns `$this`.
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
    public function toString($schemas = [], $aliases = [])
    {
        $fields = $this->dialect()->names($this->_parts['fields']);
        $sql = 'SELECT' .
            $this->_buildFlags($this->_parts['flags']) .
            $this->_buildChunk($fields ?: '*') .
            $this->_buildClause('FROM', $this->dialect()->names($this->_parts['from'])) .
            $this->_buildJoins($schemas, $aliases) .
            $this->_buildClause('WHERE', $this->dialect()->conditions($this->_parts['where'], compact('schemas', 'aliases'))) .
            $this->_buildClause('GROUP BY', $this->_group()) .
            $this->_buildClause('HAVING', $this->dialect()->conditions($this->_parts['having'], compact('schemas', 'aliases'))) .
            $this->_buildOrder() .
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
        foreach ($this->_parts['group'] as $name => $value) {
            $result[] = $this->dialect()->name($name);
        }
        return $fields = join(', ', $result);
    }

    /**
     * Build the `JOIN` clause.
     *
     * @return string The `JOIN` clause.
     */
    protected function _buildJoins($schemas, $aliases)
    {
        $joins = [];
        foreach ($this->_parts['joins'] as $value) {
            $table = $value['join'];
            $on = $value['on'];
            $type = $value['type'];
            $join = [strtoupper($type), 'JOIN'];
            $join[] = $this->dialect()->name($table);

            if ($on) {
                $join[] = 'ON';
                $join[] = $this->dialect()->conditions($on, compact('schemas', 'aliases'));
            }

            $joins[] = join(' ', $join);
        }
        return $joins ? ' ' . join(' ', $joins) : '';
    }

}
