<?php
namespace Lead\Sql\Statement\MySql;

/**
 * `SELECT` statement.
 */
class Select extends \Lead\Sql\Statement\Select
{
    /**
     * Sets `SQL_CALC_FOUND_ROWS` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function calcFoundRows($enable = true)
    {
        $this->setFlag('SQL_CALC_FOUND_ROWS', $enable);
        return $this;
    }

    /**
     * Sets `SQL_CACHE` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function cache($enable = true)
    {
        $this->setFlag('SQL_CACHE', $enable);
        return $this;
    }

    /**
     * Sets `SQL_NO_CACHE` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function noCache($enable = true)
    {
        $this->setFlag('SQL_NO_CACHE', $enable);
        return $this;
    }

    /**
     * Sets `STRAIGHT_JOIN` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function straightJoin($enable = true)
    {
        $this->setFlag('STRAIGHT_JOIN', $enable);
        return $this;
    }

    /**
     * Sets `HIGH_PRIORITY` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function highPriority($enable = true)
    {
        $this->setFlag('HIGH_PRIORITY', $enable);
        return $this;
    }

    /**
     * Sets `SQL_SMALL_RESULT` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function smallResult($enable = true)
    {
        $this->setFlag('SQL_SMALL_RESULT', $enable);
        return $this;
    }

    /**
     * Sets `SQL_BIG_RESULT` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function bigResult($enable = true)
    {
        $this->setFlag('SQL_BIG_RESULT', $enable);
        return $this;
    }

    /**
     * Sets `SQL_BUFFER_RESULT` flag.
     *
     * @param  boolean $enable A boolan value.
     * @return object          Returns `$this`.
     */
    public function bufferResult($enable = true)
    {
        $this->setFlag('SQL_BUFFER_RESULT', $enable);
        return $this;
    }
}
