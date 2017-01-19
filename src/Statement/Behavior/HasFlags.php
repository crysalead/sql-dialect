<?php
namespace Lead\Sql\Dialect\Statement\Behavior;

trait HasFlags
{
    /**
     * Sets a flag.
     *
     * @param  string  $name   The name of the flag to set.
     * @param  boolean $enable The boolean value to set.
     * @return boolean         The flag value.
     */
    public function setFlag($flag, $enable = true)
    {
        return $this->_parts['flags'][$flag] = !!$enable;
    }

    /**
     * Gets a flag.
     *
     * @param  string  $name The name of the flag to get.
     * @return boolean       The flag value.
     */
    public function getFlag($flag)
    {
        return isset($this->_parts['flags'][$flag]) ? $this->_parts['flags'][$flag] : null;
    }
}
