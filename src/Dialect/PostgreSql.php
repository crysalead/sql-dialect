<?php
namespace Lead\Sql\Dialect\Dialect;

use Lead\Set\Set;

/**
 * PostgreSQL dialect.
 */
class PostgreSql extends \Lead\Sql\Dialect\Dialect
{
    /**
     * Escape identifier character.
     *
     * @var array
     */
    protected $_escape = '"';

    /**
     * Meta attribute patterns.
     *
     * Note: by default `'escape'` is `false` and 'join' is `' '`.
     *
     * @var array
     */
    protected $_meta = [
        'table' => [
            'tablespace' => ['keyword' => 'TABLESPACE']
        ]
    ];

    /**
     * Column contraints
     *
     * @var array
     */
    protected $_constraints = [
        'primary' => ['template' => 'PRIMARY KEY ({:column})'],
        'foreign key' => [
            'template' => 'FOREIGN KEY ({:foreignKey}) REFERENCES {:to} ({:primaryKey}) {:on}'
        ],
        'unique' => [
            'template' => 'CONSTRAINT {:name} UNIQUE {:index} ({:column})'
        ],
        'check' => ['template' => '{:constraint} CHECK ({:expr})']
    ];

    /**
     * Constructor
     *
     * @param array $config The config array
     */
    public function __construct($config = [])
    {
        $defaults = [
            'classes' => [
                'select'       => 'Lead\Sql\Dialect\Statement\PostgreSql\Select',
                'insert'       => 'Lead\Sql\Dialect\Statement\PostgreSql\Insert',
                'update'       => 'Lead\Sql\Dialect\Statement\PostgreSql\Update',
                'delete'       => 'Lead\Sql\Dialect\Statement\PostgreSql\Delete',
                'create table' => 'Lead\Sql\Dialect\Statement\CreateTable',
                'drop table'   => 'Lead\Sql\Dialect\Statement\DropTable'
            ],
            'operators' => [
                ':regexp'         => ['format' => '%s ~ %s'],
                ':regexpi'        => ['format' => '%s ~* %s'],
                ':not regexp'     => ['format' => '%s !~ %s'],
                ':not regexpi'    => ['format' => '%s !~* %s'],
                ':square root'    => ['format' => '|/ %s'],
                ':cube root'      => ['format' => '||/ %s'],
                ':fact'           => ['format' => '!! %s'],
                '|/'              => ['format' => '|/ %s'],
                '||/'             => ['format' => '||/ %s'],
                '!!'              => ['format' => '!! %s'],
                ':concat'         => ['format' => '%s || %s'],
                ':pow'            => ['format' => '%s ^ %s'],
                '@'               => ['format' => '@ %s'],
                // Algebraic operations
                ':union'          => ['builder' => 'set'],
                ':union all'      => ['builder' => 'set'],
                ':except'         => ['builder' => 'set'],
                ':except all'     => ['builder' => 'set'],
                ':intersect'      => ['builder' => 'set'],
                ':intersect all'  => ['builder' => 'set']
            ]
        ];

        $config = Set::merge($defaults, $config);
        parent::__construct($config);

        $this->type('id',       ['use' => 'integer']);
        $this->type('serial',   ['use' => 'serial', 'serial' => true]);
        $this->type('string',   ['use' => 'varchar', 'length' => 255]);
        $this->type('text',     ['use' => 'text']);
        $this->type('integer',  ['use' => 'integer']);
        $this->type('boolean',  ['use' => 'boolean']);
        $this->type('float',    ['use' => 'real']);
        $this->type('decimal',  ['use' => 'numeric', 'precision' => 2]);
        $this->type('date',     ['use' => 'date']);
        $this->type('time',     ['use' => 'time']);
        $this->type('datetime', ['use' => 'timestamp']);
        $this->type('binary',   ['use' => 'bytea']);

        $this->map('bit',                         'string');
        $this->map('bool',                        'boolean');
        $this->map('boolean',                     'boolean');
        $this->map('box',                         'string');
        $this->map('bytea',                       'binary');
        $this->map('char',                        'string');
        $this->map('character',                   'string');
        $this->map('character varying',           'string');
        $this->map('cidr',                        'string');
        $this->map('circle',                      'string');
        $this->map('date',                        'date');
        $this->map('decimal',                     'string');
        $this->map('float4',                      'float');
        $this->map('float8',                      'float');
        $this->map('inet',                        'string');
        $this->map('int2',                        'integer');
        $this->map('int4',                        'integer');
        $this->map('int8',                        'integer');
        $this->map('integer',                     'integer');
        $this->map('json',                        'string');
        $this->map('lseg',                        'string');
        $this->map('line',                        'string');
        $this->map('macaddr',                     'string');
        $this->map('numeric',                     'decimal');
        $this->map('path',                        'string');
        $this->map('polygon',                     'string');
        $this->map('real',                        'float');
        $this->map('serial',                      'serial');
        $this->map('string',                      'string');
        $this->map('text',                        'string');
        $this->map('time',                        'time');
        $this->map('time with time zone',         'time');
        $this->map('time without time zone',      'time');
        $this->map('timestamp',                   'datetime');
        $this->map('timestamp with time zone',    'datetime');
        $this->map('timestamp without time zone', 'datetime');
        $this->map('timestamptz',                 'datetime');
        $this->map('tsquery',                     'string');
        $this->map('tsvector',                    'string');
        $this->map('txid_snapshot',               'string');
        $this->map('uuid',                        'string');
        $this->map('varbit',                      'string');
        $this->map('varchar',                     'string');
        $this->map('xml',                         'string');
    }

    /**
     * Helper for creating columns
     *
     * @see    chaos\source\sql\Dialect::column()
     *
     * @param  array  $field A field array
     * @param  array  $meta  The table meta data for charset & collation.
     * @return string        The SQL column string
     */
    protected function _column($field, $meta = [])
    {
        extract($field);
        if ($type === 'float' && $precision) {
            $use = 'numeric';
        }

        $column = $this->name($name) . ' ' . $this->_formatColumn($use, $length, $precision);

        if (in_array(strtolower($use), ['text', 'char', 'varchar'], true)) {
            $field += array_intersect_key($meta, array_flip(['collate']));
        }

        $result = [$column];
        $result[] = $this->meta('column', $field, ['collate']);

        if (!empty($serial)) {
            $result[] = 'NOT NULL';
        } else {
            $result[] = is_bool($null) ? ($null ? 'NULL' : 'NOT NULL') : '' ;
            if ($default !== null) {
                if (is_array($default)) {
                    $operator = key($default);
                    $default = current($default);
                } else {
                    $operator = ':value';
                }
                $states = compact('field');
                $result[] = 'DEFAULT ' . $this->format($operator, $default, $states);
            }
        }

        return join(' ', array_filter($result));
    }
}
