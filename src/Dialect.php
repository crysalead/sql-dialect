<?php
namespace Lead\Sql;

use stdClass;
use Lead\Set\Set;
use Lead\Text\Text;
use Lead\Sql\SqlException;

/**
 * ANSI SQL dialect
 */
class Dialect
{
    /**
     * Class dependencies.
     *
     * @var array
     */
    protected $_classes = [];

    /**
     * Type mapping.
     *
     * @var array
     */
     protected $_maps = [];

    /**
     * Quoter handler.
     *
     * @var Closure
     */
    protected $_quoter = null;

    /**
     * Casting handler.
     *
     * @var Closure
     */
    protected $_caster = null;

    /**
     * Quoting identifier character.
     *
     * @var string
     */
    protected $_escape = '"';

    /**
     * Date format.
     *
     * @var string
     */
    protected $_dateFormat = 'Y-m-d H:i:s';

    /**
     * Column type definitions.
     *
     * @var array
     */
    protected $_types = [];

    /**
     * List of SQL operators, paired with handling options.
     *
     * @var array
     */
    protected $_operators = [];

    /**
     * Operator builders
     *
     * @var array
     */
    protected $_builders = [];

    /**
     * List of formatter operators
     *
     * @var array
     */
    protected $_formatters = [];

    /**
     * Constructor
     *
     * @param array $config The config array.
     */
    public function __construct($config = [])
    {
        $defaults = [
            'classes' => [
                'select'       => 'Lead\Sql\Statement\Select',
                'insert'       => 'Lead\Sql\Statement\Insert',
                'update'       => 'Lead\Sql\Statement\Update',
                'delete'       => 'Lead\Sql\Statement\Delete',
                'truncate'     => 'Lead\Sql\Statement\Truncate',
                'create table' => 'Lead\Sql\Statement\CreateTable',
                'drop table'   => 'Lead\Sql\Statement\DropTable'
            ],
            'quoter' => null,
            'caster' => null,
            'types' => [],
            'operators' => $this->_defaultOperators(),
            'builders' => $this->_defaultBuilders(),
            'formatters' => $this->_defaultFormatters(),
            'dateFormat' => 'Y-m-d H:i:s'
        ];

        $config = Set::merge($defaults, $config);

        $this->_classes = $config['classes'];
        $this->_quoter = $config['quoter'];
        $this->_caster = $config['caster'];
        $this->_dateFormat = $config['dateFormat'];
        $this->_types = $config['types'];
        $this->_builders = $config['builders'];
        $this->_formatters = $config['formatters'];
        $this->_operators = $config['operators'];
    }

    /**
     * Returns supported operators.
     *
     * @return array
     */
    protected function _defaultOperators()
    {
        return [
            '='            => ['null' => ':is'],
            '<=>'          => [],
            '<'            => [],
            '>'            => [],
            '<='           => [],
            '>='           => [],
            '!='           => ['null' => ':is not'],
            '<>'           => [],
            '-'            => [],
            '+'            => [],
            '*'            => [],
            '/'            => [],
            '%'            => [],
            '>>'           => [],
            '<<'           => [],
            ':='           => [],
            '&'            => [],
            '|'            => [],
            ':mod'         => [],
            ':div'         => [],
            ':like'        => [],
            ':not like'    => [],
            ':is'          => [],
            ':is not'      => [],
            ':distinct'    => ['builder' => 'prefix'],
            '~'            => ['builder' => 'prefix'],
            ':between'     => ['builder' => 'between'],
            ':not between' => ['builder' => 'between'],
            ':in'          => ['builder' => 'list'],
            ':not in'      => ['builder' => 'list'],
            ':exists'      => ['builder' => 'list'],
            ':not exists'  => ['builder' => 'list'],
            ':all'         => ['builder' => 'list'],
            ':any'         => ['builder' => 'list'],
            ':some'        => ['builder' => 'list'],
            ':as'          => ['builder' => 'alias'],
            // logical operators
            ':not'         => ['builder' => 'prefix'],
            ':and'         => [],
            ':or'          => [],
            ':xor'         => [],
            '()'           => ['format' => '(%s)']
        ];
    }

    /**
     * Returns operator builders.
     *
     * @return array
     */
    protected function _defaultBuilders()
    {
        return [
            'function' => function ($operator, $parts) {
                $operator = strtoupper(substr($operator, 0, -2));
                return "{$operator}(" . join(", ", $parts). ')';
            },
            'prefix' => function ($operator, $parts) {
                return "{$operator} " . reset($parts);
            },
            'list' => function ($operator, $parts) {
                $key = array_shift($parts);
                return "{$key} {$operator} (" . join(", ", $parts) . ')';
            },
            'between' => function ($operator, $parts) {
                $key = array_shift($parts);
                return "{$key} {$operator} " . reset($parts) . ' AND ' . end($parts);
            },
            'set' => function ($operator, $parts) {
                return join(" {$operator} ", $parts);
            },
            'alias' => function ($operator, $parts) {
                $expr = array_shift($parts);
                return "({$expr}) {$operator} " . array_shift($parts);
            }
        ];
    }

    /**
     * Returns formatters.
     *
     * @return array
     */
    protected function _defaultFormatters()
    {
        return [
            ':name' => function ($value, $states) {
                return $this->name($value);
            },
            ':value' => function ($value, $states) {
                return $this->value($value, $states);
            },
            ':plain' => function ($value, $states) {
                return (string) $value;
            }
        ];
    }

    /**
     * Gets/sets the quoter handler.
     *
     * @param  Closure $quoter The quoter handler.
     * @return Closure         Returns the quoter handler.
     */
    public function quoter($quoter = null)
    {
        if ($quoter !== null) {
            $this->_quoter = $quoter;
        }
        return $this->_quoter;
    }

    /**
     * Gets/sets the casting handler.
     *
     * @param  Closure $caster The casting handler.
     * @return Closure         Returns the casting handler.
     */
    public function caster($caster = null)
    {
        if ($caster !== null) {
            $this->_caster = $caster;
        }
        return $this->_caster;
    }

    /**
     * Gets/sets an internal type definition.
     *
     * @param  string $type   The type name.
     * @param  array  $config The type definition.
     * @return array          Return the type definition.
     */
    public function type($type, $config = null)
    {
        if ($config) {
            $this->_types[$type] = $config;
        }
        if (!isset($this->_types[$type])) {
            throw new SqlException("Column type `'{$type}'` does not exist.");
        }
        return $this->_types[$type];
    }

    /**
     * Sets a type mapping.
     *
     * @param  string $type   The type name.
     * @param  array  $config The type definition.
     * @return array          Return the type definition.
     */
    public function map($use, $type, $options = [])
    {
        if (!isset($this->_maps[$use])) {
            $this->_maps[$use] = [];
        }
        if ($options) {
            $this->_maps[$use] = array_merge([$type => $options], $this->_maps[$use]);
        } else {
            $this->_maps[$use] += [$type => []];
        }
    }

    /**
     * Gets a mapped type.
     *
     * @param  array $options The column definition or the database type.
     * @param  array $config  The type definition.
     * @return array          Return the type definition.
     */
    public function mapped($options)
    {
        if (is_array($options)) {
            $use = $options['use'];
            unset($options['use']);
        } else {
            $use = $options;
            $options = [];
        }

        if (!isset($this->_maps[$use])) {
            return 'string';
        }

        foreach ($this->_maps[$use] as $type => $value) {
            if (!array_diff_assoc($value, $options)) {
                return $type;
            }
        }
        return 'string';
    }

    /**
     * Formats a field definition.
     *
     * @param  array $field A partial field definition.
     * @return array        A complete field definition.
     */
    public function field($field)
    {
        if (!isset($field['name'])) {
            throw new SqlException("Missing column name.");
        }
        if (isset($field['type'])) {
            $field += $this->type($field['type']);
        } elseif (!isset($field['use'])) {
            $field += $this->type('string');
        }
        return $field + [
            'name'      => null,
            'type'      => null,
            'length'    => null,
            'precision' => null,
            'serial'    => false,
            'default'   => null,
            'null'      => null
        ];
    }

    /**
     * SQL statement factory.
     *
     * @param  string $name   The name of the statement to instantiate.
     * @param  array  $config The configuration options.
     * @return object         A statement instance.
     */
    public function statement($name, $config = [])
    {
        $defaults = ['dialect' => $this];
        $config += $defaults;

        if (!isset($this->_classes[$name])) {
            throw new SqlException("Unsupported statement `'{$name}'`.");
        }
        $statement = $this->_classes[$name];
        return new $statement($config);
    }

    /**
     * Generates a list of escaped table/field names identifier.
     *
     * @param  array  $fields The fields to format.
     * @return string         The formatted fields.
     */
    public function names($fields)
    {
        return (string) join(", ", $this->escapes($fields, ''));
    }

    /**
     * Escapes a list of identifers.
     *
     * Note: it ignores duplicates.
     *
     * @param  string|array $names  A name or an array of names to escapes.
     * @param  string       $prefix An optionnal table/alias prefix to use.
     * @return array                An array of escaped fields.
     */
    public function escapes($names, $prefix)
    {
        $names = is_array($names) ? $names : [$names];
        $sql = [];
        foreach ($names as $key => $value) {
            if ($this->isOperator($key)) {
                $sql[] = $this->conditions($names);
            } elseif (is_string($value)) {
                if (!is_numeric($key)) {
                    $name = $this->name($key);
                    $value = $this->name($value);
                    $name = $name !== $value ? "{$name} AS {$value}" : $name;
                } else {
                    $name = $this->name($value);
                }
                $name = $prefix ? "{$prefix}.{$name}" : $name;
                $sql[$name] = $name;
            } elseif (!is_array($value)) {
                $sql[] = (string) $value;
            } else {
                $pfx = $prefix;
                if (!is_numeric($key)) {
                    $pfx = $this->escape($key);
                }
                $sql = array_merge($sql, $this->escapes($value, $pfx));
            }
        }
        return $sql;
    }

    /**
     * Prefixes a list of identifers.
     *
     * @param  string|array $names       A name or an array of names to prefix.
     * @param  string       $prefix      The prefix to use.
     * @param  boolean      $prefixValue Boolean indicating if prefixing must occurs.
     * @return array                     The prefixed names.
     */
    public function prefix($data, $prefix, $prefixValue = true)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ($this->isOperator($key)) {
                if ($key === ':name') {
                    $value = $this->_prefix($value, $prefix);
                } else {
                    $value = is_array($value) ? $this->prefix($value, $prefix, false) : $value;
                }
                $result[$key] = $value;
                continue;
            }
            if (!is_numeric($key)) {
                $key = $this->_prefix($key, $prefix);
            } elseif (is_array($value)) {
                $value = $this->prefix($value, $prefix, false);
            } elseif ($prefixValue) {
                $value = $this->_prefix($value, $prefix);
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Prefixes a identifer.
     *
     * @param  string $names  The name to prefix.
     * @param  string $prefix The prefix.
     * @return string         The prefixed name.
     */
    public function _prefix($name, $prefix)
    {
        list($alias, $field) = $this->undot($name);
        return $alias ? $name : "{$prefix}.{$field}";
    }

    /**
     * Returns a string of formatted conditions to be inserted into the query statement. If the
     * query conditions are defined as an array, key pairs are converted to SQL strings.
     *
     * Conversion rules are as follows:
     *
     * - If `$key` is numeric and `$value` is a string, `$value` is treated as a literal SQL
     *   fragment and returned.
     *
     * @param  array $conditions The conditions for this query.
     * @param  array $options    The options. Possible values are:
     *                            - `prepend`  _string_: The string to prepend or `false` for no prefix.
     *                            - `operator` _string_: The join operator.
     *                            - `schemas`  _array_ : The schemas hash object.
     * @return string            Returns an SQL conditions clause.
     */
    public function conditions($conditions, $options = [])
    {
        if (!$conditions) {
            return '';
        }
        $defaults = [
            'prepend' => false,
            'operator' => ':and',
            'schemas' => [],
            'schema' => null,
            'name' => null,
        ];
        $options += $defaults;

        if (!is_numeric(key($conditions))) {
            $conditions = [$conditions];
        }

        $result = $this->_operator(strtolower($options['operator']), $conditions, $options);
        return ($options['prepend'] && $result) ? "{$options['prepend']} {$result}" : $result;
    }

    /**
     * Build a SQL operator statement.
     *
     * @param  string $operator   The operator.
     * @param  array  $conditions The data for the operator.
     * @param  array  $states     The current states..
     * @return string             Returns a SQL string.
     */
    protected function _operator($operator, $conditions, $states)
    {
        if (substr($operator, -2) === '()') {
            $config = ['builder' => 'function'];
        } else if (isset($this->_operators[$operator])) {
            $config = $this->_operators[$operator];
        } else {
            throw new SqlException("Unexisting operator `'{$operator}'`.");
        }
        $parts = $this->_conditions($conditions, $states);

        $operator = (is_array($parts) && next($parts) === 'NULL' && isset($config['null'])) ? $config['null'] : $operator;
        $operator = $operator[0] === ':' ? strtoupper(substr($operator, 1)) : $operator;

        if (!isset($config['builder'])) {
            return join(" {$operator} ", $parts);
        }
        $builder = $this->_builders[$config['builder']];
        return $builder($operator, $parts);
    }

    /**
     * Checks whether a string is an operator or not.
     *
     * @param  string  $operator The operator name.
     * @return boolean           Returns `true` is the passed string is an operator, `false` otherwise.
     */
    public function isOperator($operator)
    {
        return ($operator && $operator[0] === ':') || isset($this->_operators[$operator]);
    }

    /**
     * Build a formated array of SQL statement.
     *
     * @param  array $conditions A array of conditions.
     * @param  array $states     The states.
     * @return array             Returns a array of SQL string.
     */
    protected function _conditions($conditions, $states)
    {
        $parts = [];
        foreach ($conditions as $name => $value) {
            $operator = strtolower($name);
            if (isset($this->_formatters[$operator])) {
                $parts[] = $this->format($operator, $value, $states);
            } elseif ($this->isOperator($operator)) {
                $parts[] = $this->_operator($operator, $value, $states);
            } elseif (is_numeric($name)) {
                if (is_array($value)) {
                    $parts = array_merge($parts, $this->_conditions($value, $states));
                } else {
                    $parts[] = $this->value($value, $states);
                }
            } else {
                $parts[] = $this->_name($name, $value, $states);
            }
        }
        return $parts;
    }

    /**
     * Build a <fieldname> = <value> SQL condition.
     *
     * @param  string $name   The field name.
     * @param  mixed  $value  The data value.
     * @param  array  $states The current states.
     * @return string         Returns a SQL string.
     */
    protected function _name($name, $value, &$states)
    {
        list($alias, $field) = $this->undot($name);
        $escaped = $this->name($name);
        $schema = isset($states['schemas'][$alias]) ? $states['schemas'][$alias] : null;
        $states['name'] = $field;
        $states['schema'] = $schema;

        if (!is_array($value)) {
            return $this->_operator('=', [[':name' => $name], $value], $states);
        }

        $operator = strtolower(key($value));
        if (isset($this->_formatters[$operator])) {
            return "{$escaped} = " . $this->format($operator, current($value), $states);
        } elseif (!isset($this->_operators[$operator])) {
            return $this->_operator(':in', [[':name' => $name], $value], $states);
        }

        $conditions = current($value);
        $conditions = (array) $conditions;
        array_unshift($conditions, [':name' => $name]);
        return $this->_operator($operator, $conditions, $states);
    }

    /**
     * SQL formatter.
     *
     * @param  string $operator The format operator.
     * @param  mixed  $value    The value to format.
     * @param  array  $states   The current states.
     * @return string           Returns a SQL string.
     */
    public function format($operator, $value, $states = [])
    {
        if (!isset($this->_formatters[$operator])) {
            throw new SqlException("Unexisting formatter `'{$operator}'`.");
        }
        $formatter = $this->_formatters[$operator];
        return $formatter($value, $states);
    }

    /**
     * Escapes a column/table/schema with dotted syntax support.
     *
     * @param  string $name The identifier name.
     * @return string       The escaped identifier.
     */
    public function name($name)
    {
        if (!is_string($name)) {
            return $this->names($name);
        }
        list($alias, $field) = $this->undot($name);
        return $alias ? $this->escape($alias) . '.' . $this->escape($field) : $this->escape($name);
    }

    /**
     * Escapes a column/table/schema name.
     *
     * @param  string $name The identifier name.
     * @return string       The escaped identifier.
     */
    public function escape($name)
    {
        return $name === '*' ? '*' : $this->_escape . $name . $this->_escape;
    }

    /**
     * Split dotted syntax into distinct name.
     *
     * @param  string $field A dotted identifier.
     * @return array         The parts.
     */
    public function undot($field)
    {
        if (is_string($field) && (($pos = strrpos($field, ".")) !== false)) {
            return [substr($field, 0, $pos), substr($field, $pos + 1)];
        }
        return [null, $field];
    }

    /**
     * Quotes a string.
     *
     * @param  string $string The string to quote.
     * @return string         The quoted string.
     */
    public function quote($string)
    {
        if ($quoter = $this->quoter()) {
            return $quoter($string);
        }
        $replacements = array(
            "\x00"=>'\x00',
            "\n"=>'\n',
            "\r"=>'\r',
            "\\"=>'\\\\',
            "'"=>"\'",
            "\x1a"=>'\x1a'
        );
        return "'" . strtr(addcslashes($string, '%_'), $replacements) . "'";
    }

    /**
     * Converts a given value into the proper type based on a given schema definition.
     *
     * @param  mixed $value  The value to be converted. Arrays will be recursively converted.
     * @param  array $states The current states.
     * @return mixed         The formatted value.
     */
    public function value($value, $states = [])
    {
        if ($caster = $this->caster()) {
            return $caster($value, $states);
        }
        switch (true) {
            case is_null($value):
                return 'NULL';
            case is_bool($value):
                return $value ? 'TRUE' : 'FALSE';
            case is_string($value):
                return $this->quote($value);
            case is_array($value):
                $cast = function($value) use (&$cast) {
                    $result = [];
                    foreach ($value as $k => $v) {
                        if (is_array($v)) {
                            $result[] = $cast($v);
                        } else {
                            $result[] = $this->value($v);
                        }
                    }
                    return '{' . join(',', $result) . '}';

                };
                return $cast($value);
        }
        return (string) $value;
    }

    /**
     * Generates a database-native column schema string
     *
     * @param  array  $column A field array structured like the following:
     *                        `['name' => 'value', 'type' => 'value' [, options]]`, where options
     *                        can be `'default'`, `'null'`, `'length'` or `'precision'`.
     * @return string         A SQL string formated column.
     */
    public function column($field)
    {
        $field = $this->field($field);

        $isNumeric = preg_match('/^(integer|float|boolean)$/', $field['type']);
        if ($isNumeric && $field['default'] === '') {
            $field['null'] = true;
            $field['default'] = null;
        }
        $field['use'] = strtolower($field['use']);
        return $this->_column($field);
    }

    /**
     * Formats a column name.
     *
     * @param  string  $name      A column name.
     * @param  integer $length    A column length.
     * @param  integer $precision A column precision.
     * @return string             The formatted column.
     */
    public function _formatColumn($name, $length = null, $precision = null)
    {
        $size = [];
        if ($length) {
            $size[] = $length;
        }
        if ($precision) {
            $size[] = $precision;
        }
        return $size ? $name . '(' . join(',', $size) . ')' : $name;
    }

    /**
     * Builds a column/table meta.
     *
     * @param  string $type  The meta type.
     * @param  array  $data  The meta data.
     * @param  array  $names If `$names` is not `null` only build meta present in `$names`.
     * @return string        The SQL meta.
     */
    public function meta($type, $data, $names = null)
    {
        $result = [];
        $names = $names ? (array) $names : array_keys($data);
        foreach ($names as $name) {
            $value = isset($data[$name]) ? $data[$name] : null;
            if ($value && $meta = $this->_meta($type, $name, $value)) {
                $result[] = $meta;
            }
        }
        return join(' ', $result);
    }

    /**
     * Helper for building a column/table single meta string.
     *
     * @param  string $type  The type of the meta to build (possible values: 'table' or 'column')
     * @param  string $name  The name of the meta to build
     * @param  mixed  $value The meta value.
     * @return string        The SQL meta.
     */
    protected function _meta($type, $name, $value)
    {
        $meta = isset($this->_meta[$type][$name]) ? $this->_meta[$type][$name] : null;
        if (!$meta || (isset($meta['options']) && !in_array($value, $meta['options']))) {
            return;
        }
        $meta += ['keyword' => '', 'escape' => false, 'join' => ' '];
        extract($meta);
        if ($escape === true) {
            $value = $this->value($value);
        }
        $result = $keyword . $join . $value;
        return $result !== ' ' ? $result : '';
    }

    /**
     * Build a SQL column constraint
     *
     * @param  string $name       The name of the meta to build.
     * @param  mixed  $constraint The constraint value.
     * @param  array  $options    The constraint options.
     * @return string             The SQL meta string.
     */
    public function constraint($name, $value, $options = [])
    {
        $value += ['options' => []];
        $meta = isset($this->_constraints[$name]) ? $this->_constraints[$name] : null;
        if (!($template = isset($meta['template']) ? $meta['template'] : null)) {
            throw new SqlException("Invalid constraint template `'{$name}'`.");
        }

        $data = [];
        foreach ($value as $name => $value) {
            switch ($name) {
                case 'key':
                case 'index':
                    if (isset($meta[$name])) {
                        $data['index'] = $meta[$name];
                    }
                break;
                case 'to':
                    $data[$name] = $this->name($value);
                break;
                case 'on':
                    $data[$name] = "ON {$value}";
                break;
                case 'constraint':
                    $data[$name] = "CONSTRAINT " . $this->name($value);
                break;
                case 'expr':
                    $data[$name] = $this->conditions(is_array($value) ? $value : [$value], $options);
                break;
                case 'column':
                case 'primaryKey':
                case 'foreignKey':
                    $data[$name] = join(', ', array_map([$this, 'name'], (array) $value));
                break;
            }
        }

        return trim(Text::insert($template, $data, ['clean' => ['method' => 'text']]));
    }
}
