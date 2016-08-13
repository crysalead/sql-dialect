<?php
namespace Lead\Sql\Statement;

use Lead\Sql\SqlException;
use Lead\Sql\Statement\Behavior\HasFlags;
use Lead\Sql\Statement\Behavior\HasWhere;
use Lead\Sql\Statement\Behavior\HasOrder;
use Lead\Sql\Statement\Behavior\HasLimit;

/**
 * `DELETE` statement.
 */
class Delete extends \Lead\Sql\Statement
{
    use HasFlags, HasWhere, HasOrder, HasLimit;

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
            $this->_buildClause('WHERE', $this->dialect()->conditions($this->_parts['where'])) .
            $this->_buildOrder() .
            $this->_buildClause('LIMIT', $this->_parts['limit']) .
            $this->_buildClause('RETURNING', $this->dialect()->names($this->_parts['returning']));
    }

}
