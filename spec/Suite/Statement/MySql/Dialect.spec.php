<?php
namespace Lead\Sql\Dialect\Spec\Suite\Statement\MySql;

use Lead\Sql\Dialect\SqlException;
use Lead\Sql\Dialect\Dialect\MySql;

describe("MySql Dialect", function() {

    beforeEach(function() {
        $this->dialect = new MySql();
    });

    describe("->field()", function() {

        it("formats a column with `varchar` by default", function() {

            $field = $this->dialect->field(['name' => 'title']);
            expect($field)->toBe([
                'name'      => 'title',
                'use'       => 'varchar',
                'length'    => 255,
                'type'      => null,
                'precision' => null,
                'serial'    => false,
                'default'   => null,
                'null'      => null
            ]);

        });

        it("ignores invalid types when the `'use'` options is set", function() {

            $field = $this->dialect->field(['type' => 'invalid', 'name' => 'title', 'use' => 'text']);
            expect($field)->toBe([
                'type'      => 'invalid',
                'name'      => 'title',
                'use'       => 'text',
                'length'    => null,
                'precision' => null,
                'serial'    => false,
                'default'   => null,
                'null'      => null
            ]);

        });

    });

    describe("->conditions()", function() {

        it("manages set operators", function() {

            $select1 = $this->dialect->statement('select')->from('table1');
            $select2 = $this->dialect->statement('select')->from('table2');

            $part = $this->dialect->conditions([
                ':union' => [
                    $select1, $select2
                ]
            ]);
            expect($part)->toBe('SELECT * FROM `table1` UNION SELECT * FROM `table2`');

        });

    });

    describe("->meta()", function() {

        context("with table", function() {

            it("generates charset meta", function() {

                $result = $this->dialect->meta('table', ['charset' => 'utf8']);
                expect($result)->toBe('DEFAULT CHARSET utf8');

            });

            it("generates collate meta", function() {

                $result = $this->dialect->meta('table', ['collate' => 'utf8_unicode_ci']);
                expect($result)->toBe('COLLATE utf8_unicode_ci');

            });

            it("generates ENGINE meta", function() {

                $result = $this->dialect->meta('table', ['engine' => 'InnoDB']);
                expect($result)->toBe('ENGINE InnoDB');

            });

            it("generates TABLESPACE meta", function() {

                $result = $this->dialect->meta('table', ['tablespace' => 'myspace']);
                expect($result)->toBe('TABLESPACE myspace');

            });

        });

        context("with column", function() {

            it("generates charset meta", function() {

                $result = $this->dialect->meta('column', ['charset' => 'utf8']);
                expect($result)->toBe('CHARACTER SET utf8');

            });

            it("generates collate meta", function() {

                $result = $this->dialect->meta('column', ['collate' => 'utf8_unicode_ci']);
                expect($result)->toBe('COLLATE utf8_unicode_ci');

            });

            it("generates ENGINE meta", function() {

                $result = $this->dialect->meta('column', ['comment' => 'comment value']);
                expect($result)->toBe('COMMENT \'comment value\'');

            });

        });

    });

    describe("->constraint()", function() {

        context("with `'primary'`", function() {

            it("generates a PRIMARY KEY constraint", function() {

                $data = [
                    'column' => ['id']
                ];
                $result = $this->dialect->constraint('primary', $data);
                expect($result)->toBe('PRIMARY KEY (`id`)');

            });

            it("generates a multiple PRIMARY KEY constraint", function() {

                $data = ['column' => ['id', 'name']];
                $result = $this->dialect->constraint('primary', $data);
                expect($result)->toBe('PRIMARY KEY (`id`, `name`)');

            });

        });

        context("with `'unique'`", function() {

            it("generates an UNIQUE KEY constraint", function() {

                $data = [
                    'column' => 'id'
                ];
                $result = $this->dialect->constraint('unique', $data);
                expect($result)->toBe('UNIQUE (`id`)');

            });

            it("generates a multiple UNIQUE KEY constraint", function() {

                $data = [
                    'column' => ['id', 'name']
                ];
                $result = $this->dialect->constraint('unique', $data);
                expect($result)->toBe('UNIQUE (`id`, `name`)');

            });

            it("generates a multiple UNIQUE INDEX constraint", function() {

                $data = [
                    'column' => ['id', 'name'], 'index' => true
                ];
                $result = $this->dialect->constraint('unique', $data);
                expect($result)->toBe('UNIQUE INDEX (`id`, `name`)');

            });

            it("generates an UNIQUE KEY constraint when both index & key are required", function() {

                $data = [
                    'column' => ['id', 'name'], 'index' => true, 'key' => true
                ];
                $result = $this->dialect->constraint('unique', $data);
                expect($result)->toBe('UNIQUE KEY (`id`, `name`)');

            });
        });

        context("with `'check'`", function() {

            it("generates a CHECK constraint", function() {

                $data = [
                    'expr' => [
                        'population' => ['>' => 20],
                        'name' => 'Los Angeles'
                    ]
                ];
                $result = $this->dialect->constraint('check', $data);
                expect($result)->toBe("CHECK (`population` > 20 AND `name` = 'Los Angeles')");

            });

        });

        context("with `'foreign_key'`", function() {

            it("generates a FOREIGN KEY constraint", function() {

                $data = [
                    'foreignKey' => 'table_id',
                    'to' => 'table',
                    'primaryKey' => 'id',
                    'on' => 'DELETE CASCADE'
                ];
                $result = $this->dialect->constraint('foreign key', $data);
                expect($result)->toBe('FOREIGN KEY (`table_id`) REFERENCES `table` (`id`) ON DELETE CASCADE');

            });

        });

    });

    describe("->column()", function() {

        context("with a integer column", function() {

            it("generates an interger column", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'integer'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` int');

            });

            it("generates an interger column with the correct length", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'integer',
                    'length' => 11
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` int(11)');

            });

        });

        context("with a string column", function() {

            it("generates a varchar column", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'string',
                    'length' => 32,
                    'null' => true,
                    'comment' => 'test'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` varchar(32) NULL COMMENT \'test\'');

            });

            it("generates a varchar column with a default value", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'string',
                    'length' => 32,
                    'default' => 'default value'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` varchar(32) DEFAULT \'default value\'');

                $data['null'] = false;
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` varchar(32) NOT NULL DEFAULT \'default value\'');

            });

            it("generates a varchar column with charset & collate", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'string',
                    'length' => 32,
                    'null' => false,
                    'charset' => 'utf8',
                    'collate' => 'utf8_unicode_ci'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL');

            });

        });

        context("with a float column", function() {

            it("generates a float column", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'float',
                    'length' => 10
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` float(10)');

            });

            it("generates a decimal column", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'float',
                    'length' => 10,
                    'precision' => 2
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` decimal(10,2)');

            });

        });

        context("with a default value", function() {

            it("generates a default value", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'text',
                    'default' => 'value'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` text DEFAULT \'value\'');

            });

            it("overrides default value for numeric type when equal to an empty string", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'float',
                    'length' => 10,
                    'default' => ''
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` float(10) NULL');

            });

        });

        context("with a datetime column", function() {

            it("generates a datetime column", function() {

                $data = [
                    'name' => 'modified',
                    'type' => 'datetime'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`modified` datetime');

            });

            it("generates a datetime column with a default value", function() {

                $data = [
                    'name' => 'created',
                    'type' => 'datetime',
                    'default' => [':plain' => 'CURRENT_TIMESTAMP']
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`created` datetime DEFAULT CURRENT_TIMESTAMP');

            });

        });

        context("with a datetime column", function() {

            it("generates a date column", function() {

                $data = [
                    'name' => 'created',
                    'type' => 'date'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`created` date');

            });

        });

        context("with a time column", function() {

            it("generates a time column", function() {

                $data = [
                    'name' => 'created',
                    'type' => 'time'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`created` time');

            });

        });

        context("with a boolean column", function() {

            it("generates a boolean column", function() {

                $data = [
                    'name' => 'active',
                    'type' => 'boolean'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`active` boolean');

            });

            it("generates a boolean column where default is `true`", function() {

                $data = [
                    'name'    => 'active',
                    'type'    => 'boolean',
                    'default' => true
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`active` boolean DEFAULT TRUE');

            });

            it("generates a boolean column where default is `false`", function() {

                $data = [
                    'name'    => 'active',
                    'type'    => 'boolean',
                    'default' => false
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`active` boolean DEFAULT FALSE');

            });

        });

        context("with a binary column", function() {

            it("generates a binary column", function() {

                $data = [
                    'name' => 'raw',
                    'type' => 'binary'
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`raw` blob');

            });

        });

        context("with a bad type column", function() {

            it("generates throws an execption", function() {

                $closure = function() {
                    $data = [
                        'name' => 'fieldname',
                        'type' => 'invalid'
                    ];
                    $this->dialect->column($data);
                };
                expect($closure)->toThrow(new SqlException("Column type `'invalid'` does not exist."));

            });

        });

        context("with a use option", function() {

            it("overrides the default type", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'string',
                    'use' => 'decimal',
                    'length' => 11,
                    'precision' => 2
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` decimal(11,2)');

            });

        });

        context("with a default column value", function() {

            it("sets up the default value", function() {

                $data = [
                    'name' => 'fieldname',
                    'type' => 'integer',
                    'length' => 11,
                    'default' => 1
                ];
                $result = $this->dialect->column($data);
                expect($result)->toBe('`fieldname` int(11) DEFAULT 1');

            });

            context("with a casting handler defined", function() {

                beforeEach(function() {

                    $dialect = $this->dialect;

                    $dialect->caster(function($value, $states) use ($dialect) {
                        if (!isset($states['field']['type'])) {
                            return $value;
                        }
                        switch ($states['field']['type']) {
                            case 'integer':
                                return (int) $value;
                            break;
                            default:
                                return (string) $dialect->quote($value);
                            break;
                        }
                    });

                });

                it("casts the default value to an integer", function() {

                    $data = [
                        'name'    => 'fieldname',
                        'type'    => 'integer',
                        'length'  => 11,
                        'default' => '1'
                    ];
                    $result = $this->dialect->column($data);
                    expect($result)->toBe('`fieldname` int(11) DEFAULT 1');

                });

                it("casts the default value to an string", function() {

                    $data = [
                        'name'    => 'fieldname',
                        'type'    => 'string',
                        'length'  => 64,
                        'default' => 1
                    ];
                    $result = $this->dialect->column($data);
                    expect($result)->toBe('`fieldname` varchar(64) DEFAULT \'1\'');

                });

            });

        });

    });

});
