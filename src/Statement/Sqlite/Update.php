<?php
namespace Lead\Sql\Statement\Sqlite;

/**
 * `UPDATE` statement.
 */
class Update extends \Lead\Sql\Statement\Update
{
    /**
     * Sets `OR ABORT` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function orAbort($enable = true)
    {
        $this->setFlag('OR ABORT', $enable);
        return $this;
    }

    /**
     * Sets `OR FAIL` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function orFail($enable = true)
    {
        $this->setFlag('OR FAIL', $enable);
        return $this;
    }

    /**
     * Sets `OR IGNORE` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function orIgnore($enable = true)
    {
        $this->setFlag('OR IGNORE', $enable);
        return $this;
    }

    /**
     * Sets `OR REPLACE` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function orReplace($enable = true)
    {
        $this->setFlag('OR REPLACE', $enable);
        return $this;
    }

    /**
     * Sets `OR ROLLBACK` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function orRollback($enable = true)
    {
        $this->setFlag('OR ROLLBACK', $enable);
        return $this;
    }
}
