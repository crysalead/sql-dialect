<?php
namespace Lead\Sql\Dialect\Statement\MySql;

/**
 * `UPDATE` statement.
 */
class Update extends \Lead\Sql\Dialect\Statement\Update
{
    /**
     * Sets `LOW_PRIORITY` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function lowPriority($enable = true)
    {
        $this->setFlag('LOW_PRIORITY', $enable);
        return $this;
    }

    /**
     * Sets `IGNORE` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function ignore($enable = true)
    {
        $this->setFlag('IGNORE', $enable);
        return $this;
    }
}
