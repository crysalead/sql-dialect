<?php
namespace Lead\Sql\Dialect\Statement;

use Lead\Sql\Dialect\SqlException;
use Lead\Sql\Dialect\Statement\Behavior\HasFlags;
use Lead\Sql\Dialect\Statement\Behavior\HasWhere;
use Lead\Sql\Dialect\Statement\Behavior\HasOrder;
use Lead\Sql\Dialect\Statement\Behavior\HasLimit;

/**
 * `DELETE` statement.
 */
class Delete extends \Lead\Sql\Dialect\Statement
{
    use HasFlags, HasWhere, HasOrder, HasLimit;

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
        'from'      => '',
        'where'     => [],
        'order'     => [],
        'limit'     => '',
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
     * Sets the table name to create.
     *
     * @param  string $from The table name.
     * @return object       Returns `$this`.
     */
    public function from($from)
    {
        $this->_parts['from'] = $from;
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
        if (!$this->_parts['from']) {
            throw new SqlException("Invalid `DELETE` statement, missing `FROM` clause.");
        }

        return 'DELETE' .
            $this->_buildFlags($this->_parts['flags']) .
            $this->_buildClause('FROM', $this->dialect()->names($this->_parts['from'])) .
            $this->_buildClause('WHERE', $this->dialect()->conditions($this->_parts['where'], [
                'schemas' => ['' => $this->_schema]
            ])) .
            $this->_buildOrder() .
            $this->_buildClause('LIMIT', $this->_parts['limit']) .
            $this->_buildClause('RETURNING', $this->dialect()->names($this->_parts['returning']));
    }

}
