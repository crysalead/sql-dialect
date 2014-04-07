<?php
namespace sql;

/**
 * The `SqlException` is thrown when a SQL operation returns an exception.
 */
class SqlException extends \Exception
{
    protected $code = 500;
}
