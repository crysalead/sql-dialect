<?php
namespace sql\dialect;

use set\Set;

/**
 * MySQL dialect.
 */
class MySql extends \sql\Dialect
{
    /**
     * MySQL types and their associatied internal types.
     *
     * @var array
     */
     protected $_matches = [
        'boolean'            => 'boolean',
        'tinyint'            => 'integer',
        'smallint'           => 'integer',
        'mediumint'          => 'integer',
        'int'                => 'integer',
        'bigint'             => 'integer',
        'float'              => 'float',
        'double'             => 'float',
        'decimal'            => 'decimal',
        'tinytext'           => 'string',
        'char'               => 'string',
        'varchar'            => 'string',
        'time'               => 'string',
        'date'               => 'datetime',
        'datetime'           => 'datetime',
        'tinyblob'           => 'string',
        'mediumblob'         => 'string',
        'blob'               => 'string',
        'longblob'           => 'string',
        'text'               => 'string',
        'mediumtext'         => 'string',
        'longtext'           => 'string',
        'year'               => 'string',
        'bit'                => 'string',
        'geometry'           => 'string',
        'point'              => 'string',
        'multipoint'         => 'string',
        'linestring'         => 'string',
        'multilinestring'    => 'string',
        'polygon'            => 'string',
        'multipolygon'       => 'string',
        'geometrycollection' => 'string'
    ];

    /**
     * Column type definitions.
     *
     * @var array
     */
    protected $_types = [];

    /**
     * Escape identifier character.
     *
     * @var array
     */
    protected $_escape = '`';

    /**
     * Meta attribute syntax pattern.
     *
     * Note: by default `'escape'` is false and 'join' is `' '`.
     *
     * @var array
     */
    protected $_meta = [
        'column' => [
            'charset' => ['keyword' => 'CHARACTER SET'],
            'collate' => ['keyword' => 'COLLATE'],
            'comment' => ['keyword' => 'COMMENT', 'escape' => true]
        ],
        'table' => [
            'charset' => ['keyword' => 'DEFAULT CHARSET'],
            'collate' => ['keyword' => 'COLLATE'],
            'engine' => ['keyword' => 'ENGINE'],
            'tablespace' => ['keyword' => 'TABLESPACE']
        ]
    ];

    /**
     * Column contraints template
     *
     * @var array
     */
    protected $_constraints = [
        'primary' => ['template' => 'PRIMARY KEY ({:column})'],
        'foreign key' => [
            'template' => 'FOREIGN KEY ({:foreignKey}) REFERENCES {:to} ({:primaryKey}) {:on}'
        ],
        'index' => ['template' => 'INDEX ({:column})'],
        'unique' => [
            'template' => 'UNIQUE {:index} ({:column})',
            'key' => 'KEY',
            'index' => 'INDEX'
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
                'select'       => 'sql\statement\mysql\Select',
                'insert'       => 'sql\statement\mysql\Insert',
                'update'       => 'sql\statement\mysql\Update',
                'delete'       => 'sql\statement\mysql\Delete',
                'create table' => 'sql\statement\CreateTable',
                'drop table'   => 'sql\statement\DropTable'
            ],
            'operators' => [
                '#'            => ['format' => '%s ^ %s'],
                ':regex'       => ['format' => '%s REGEXP %s'],
                ':rlike'       => [],
                ':sounds like' => [],
                // Algebraic operations
                ':union'       => ['type' => 'set'],
                ':union all'   => ['type' => 'set'],
                ':minus'       => ['type' => 'set'],
                ':except'      => ['name' => 'MINUS', 'type' => 'set']
            ]
        ];
        $config = Set::merge($defaults, $config);
        parent::__construct($config);

        $this->type('id',       ['use' => 'int']);
        $this->type('serial',   ['use' => 'int', 'serial' => true]);
        $this->type('string',   ['use' => 'varchar', 'length' => 255]);
        $this->type('text',     ['use' => 'text']);
        $this->type('integer',  ['use' => 'int']);
        $this->type('boolean',  ['use' => 'boolean']);
        $this->type('float',    ['use' => 'float']);
        $this->type('decimal',  ['use' => 'decimal', 'precision' => 2]);
        $this->type('date',     ['use' => 'date']);
        $this->type('time',     ['use' => 'time']);
        $this->type('datetime', ['use' => 'datetime']);
        $this->type('binary',   ['use' => 'blob']);
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
            $use = 'decimal';
        }

        $column = $this->name($name) . ' ' . $use;

        $allowPrecision = preg_match('/^(decimal|float|double|real|numeric)$/', $use);
        $precision = ($precision && $allowPrecision) ? ",{$precision}" : '';

        if ($length && ($allowPrecision || preg_match('/(char|binary|int|year)/',$use))) {
            $column .= "({$length}{$precision})";
        }

        $result = [$column];
        $result[] = $this->meta('column', $field, ['charset', 'collate']);

        if (!empty($serial)) {
            $result[] = 'NOT NULL AUTO_INCREMENT';
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

        $result[] = $this->meta('column', $field, ['comment']);
        return join(' ', array_filter($result));
    }
}
