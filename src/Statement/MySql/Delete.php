<?php
namespace Lead\Sql\Statement\MySql;

/**
 * `DELETE` statement.
 */
class Delete extends \Lead\Sql\Statement\Delete
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

    /**
     * Sets `QUICK` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function quick($enable = true)
    {
        $this->setFlag('QUICK', $enable);
        return $this;
    }

}
