<?php
namespace Lead\Sql\Dialect\Spec\Suite\Statement;

use Lead\Sql\Dialect\SqlException;
use Lead\Sql\Dialect\Dialect;

use Kahlan\Plugin\Stub;

describe("Update", function() {

    beforeEach(function() {
        $this->dialect = new Dialect();
        $this->update = $this->dialect->statement('update');
    });

    describe("->table()", function() {

        it("sets the table name", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value']);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\'');

        });

        it("throws an exception if the `TABLE` clause is missing", function() {

            $closure = function() {
                $this->update
                     ->values(['field' => 'value'])
                     ->toString();
            };
            expect($closure)->toThrow(new SqlException("Invalid `UPDATE` statement, missing `TABLE` clause."));

        });

        it("throws an exception if the `VALUES` clause is missing", function() {

            $closure = function() {
                $this->update
                     ->table('table')
                     ->toString();
            };
            expect($closure)->toThrow(new SqlException("Invalid `UPDATE` statement, missing `VALUES` clause."));

        });

    });

    describe("->values()", function() {

        it("assures the custom casting handler is correctly called if set", function() {

            $getType = function($field){};

            $caster = function($value, $states) use ($getType) {
              expect($states['name'])->toBe('field');
              expect($states['schema'])->toBe($getType);
              expect($value)->toBe('value');
              return "'casted'";
            };

            $this->dialect->caster($caster);
            $update = $this->dialect->statement('update', ['schema' => $getType]);
            $update->table('table')->values(['field' => 'value']);

            expect($update->toString())->toBe('UPDATE "table" SET "field" = \'casted\'');

        });

    });

    describe("->where()", function() {

        it("sets a `WHERE` clause", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->where([true]);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' WHERE TRUE');

        });

        it("assures the custom casting handler is correctly called if set", function() {

            $getType = function($field) {
                if ($field === 'field') {
                    return 'fieldType';
                }
            };

            $caster = function($value, $states) use ($getType) {
              $type = $states['schema']($states['name']);
              return $type === 'fieldType' ? "'casted'" : $value;
            };

            $this->dialect->caster($caster);
            $update = $this->dialect->statement('update', ['schema' => $getType]);
            $update->table('table')->values(['field' => 'value'])->where(['field' => 'value']);

            expect($update->toString())->toBe('UPDATE "table" SET "field" = \'casted\' WHERE "field" = \'casted\'');

        });

    });

    describe("->order()", function() {

        it("sets an `ORDER BY` clause", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->order('field');

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' ORDER BY "field" ASC');

        });

        it("sets an `ORDER BY` clause with a `'DESC'` direction", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->order(['field' => 'DESC']);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' ORDER BY "field" DESC');

        });

        it("sets an a `ORDER BY` clause with a `'DESC'` direction (compatibility syntax)", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->order('field DESC');

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' ORDER BY "field" DESC');

        });

        it("sets an a `ORDER BY` clause with multiple fields", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->order(['field1' => 'ASC', 'field2' => 'DESC']);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' ORDER BY "field1" ASC, "field2" DESC');

        });

        it("sets an a `ORDER BY` clause with multiple fields using multiple call", function() {

            $this->update->table('table')
                ->values(['field' => 'value'])
                ->order(['field1' => 'ASC'])
                ->order(['field2' => 'DESC']);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' ORDER BY "field1" ASC, "field2" DESC');

        });

        it("ignores empty parameters", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->order()
                ->order('')
                ->order([])
                ->order(null);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\'');

        });

    });

    describe("->limit()", function() {

        it("generates a `LIMIT` statement", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->limit(50);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' LIMIT 50');

        });

        it("generates a `LIMIT` statement with a offset value", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->limit(50, 10);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' LIMIT 50 OFFSET 10');

        });

        it("doesn't generate an `ORDER BY` with an invalid field names", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->limit()
                ->limit(0, 0);

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\'');

        });

    });

    describe("->__toString()" , function() {

        it("casts object to string query", function() {

            $this->update->table('table')->values(['field' => 'value']);;
            $query = 'UPDATE "table" SET "field" = \'value\'';
            expect($this->update)->not->toBe($query);
            expect((string) $this->update)->toBe($query);
            expect("{$this->update}")->toBe($query);

        });

    });

});