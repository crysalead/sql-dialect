<?php
namespace Lead\Sql\Dialect\Statement\PostgreSql;

use Lead\Sql\Dialect\SqlException;

/**
 * `SELECT` statement.
 */
class Select extends \Lead\Sql\Dialect\Statement\Select
{
    /**
     * Set the lock mode.
     *
     * @param  boolean $mode The lock mode.
     * @return object        Returns `$this`.
     */
    public function lock($mode = 'update')
    {
        switch (strtolower($mode)) {
            case 'update':
                $lock = 'FOR UPDATE';
                break;
            case 'share':
                $lock = 'FOR SHARE';
                break;
            case 'no key update':
                $lock = 'FOR NO KEY UPDATE';
                break;
            case 'key share':
                $lock = 'FOR KEY SHARE';
                break;
            case false:
                $lock = false;
                break;
            default:
                throw new SqlException("Invalid PostgreSQL lock mode `'{$mode}'`.");
                break;
        }
        $this->_parts['lock'] = $lock;
        return $this;
    }
}
