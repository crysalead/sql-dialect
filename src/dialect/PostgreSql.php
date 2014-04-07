<?php
namespace sql\dialect;

use set\Set;

/**
 * PostgreSQL dialect.
 */
class PostgreSql extends \sql\Dialect
{
    /**
     * PostgreSql types matching
     *
     * @var array
     */
    protected $_matches = [
        'bool'          => 'boolean',
        'boolean'       => 'boolean',
        'int2'          => 'integer',
        'int4'          => 'integer',
        'int8'          => 'integer',
        'integer'       => 'integer',
        'real'          => 'float',
        'float4'        => 'float',
        'float8'        => 'float',
        'bytea'         => 'binary',
        'numeric'       => 'decimal',
        'text'          => 'string',
        'char'          => 'string',
        'character'     => 'string',
        'varying'       => 'string',
        'varchar'       => 'string',
        'macaddr'       => 'string',
        'inet'          => 'string',
        'cidr'          => 'string',
        'string'        => 'string',
        'date'          => 'date',
        'time'          => 'time',
        'timestamp'     => 'datetime',
        'timestamptz'   => 'datetime',
        'lseg'          => 'string',
        'path'          => 'string',
        'box'           => 'string',
        'polygon'       => 'string',
        'line'          => 'string',
        'circle'        => 'string',
        'bit'           => 'string',
        'varbit'        => 'string',
        'decimal'       => 'string',
        'uuid'          => 'string',
        'tsvector'      => 'string',
        'tsquery'       => 'string',
        'txid_snapshot' => 'string',
        'json'          => 'string',
        'uuid'          => 'string',
        'xml'           => 'string',
        'serial'        => 'serial'
    ];

    /**
     * Escape identifier character.
     *
     * @var array
     */
    protected $_escape = '"';

    /**
     * Meta attribute syntax pattern.
     *
     * Note: by default `'escape'` is false and 'join' is `' '`.
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
            'template' => 'UNIQUE {:index} ({:column})'
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
                'select'       => 'sql\statement\postgresql\Select',
                'insert'       => 'sql\statement\postgresql\Insert',
                'update'       => 'sql\statement\postgresql\Update',
                'delete'       => 'sql\statement\postgresql\Delete',
                'create table' => 'sql\statement\CreateTable',
                'drop table'   => 'sql\statement\DropTable'
            ],
            'operators' => [
                ':regex'          => ['format' => '%s ~ %s'],
                ':regexi'         => ['format' => '%s ~* %s'],
                ':not regex'      => ['format' => '%s !~ %s'],
                ':not regexi'     => ['format' => '%s !~* %s'],
                ':similar to'     => [],
                ':not similar to' => [],
                ':square root'    => ['format' => '|/ %s'],
                ':cube root'      => ['format' => '||/ %s'],
                ':fact'           => ['format' => '!! %s'],
                '|/'              => ['format' => '|/ %s'],
                '||/'             => ['format' => '||/ %s'],
                '!!'              => ['format' => '!! %s'],
                ':concat'         => ['format' => '%s || %s'],
                ':pow'            => ['format' => '%s ^ %s'],
                '#'               => [],
                '@'               => ['format' => '@ %s'],
                '<@'              => [],
                '@>'              => [],
                // Algebraic operations
                ':union'          => ['type' => 'set'],
                ':union all'      => ['type' => 'set'],
                ':except'         => ['type' => 'set'],
                ':except all'     => ['type' => 'set'],
                ':intersect'      => ['type' => 'set'],
                ':intersect all'  => ['type' => 'set']
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
    }

    /**
     * Helper for creating columns
     *
     * @see    chaos\source\sql\Dialect::column()
     * @param  array $field A field array
     * @return string The SQL column string
     */
    protected function _column($field)
    {
        extract($field);
        if ($type === 'float' && $precision) {
            $use = 'numeric';
        }

        $column = $this->name($name) . ' ' . $use;

        $allowPrecision = preg_match('/^(decimal|float|double|real|numeric)$/', $use);
        $precision = ($precision && $allowPrecision) ? ",{$precision}" : '';

        if ($length && ($allowPrecision || preg_match('/char|numeric|interval|bit|time/',$use))) {
            $column .= "({$length}{$precision})";
        }

        $result = [$column];

        if (!empty($serial)) {
            $result[] = 'NOT NULL';
        } else {
            $result[] = is_bool($null) ? ($null ? 'NULL' : 'NOT NULL') : '' ;
            if ($default) {
                if (is_array($default)) {
                    list($operator, $default) = each($default);
                } else {
                    $operator = ':value';
                }
                $result[] = 'DEFAULT ' . $this->format($operator, $default, compact('field'));
            }
        }

        return join(' ', array_filter($result));
    }
}
