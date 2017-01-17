<?php
namespace Lead\Sql\Spec\Suite\Statement;

use Lead\Sql\SqlException;
use Lead\Sql\Dialect;

use Kahlan\Plugin\Stub;

describe("Insert", function() {

    beforeEach(function() {
        $this->dialect = new Dialect();
        $this->insert = $this->dialect->statement('insert');
    });

    describe("->into()", function() {

        it("sets the `INTO` clause", function() {

            $this->insert->into('table')->values([
                'field1' => 'value1',
                'field2' => 'value2'
            ]);
            expect($this->insert->toString())->toBe('INSERT INTO "table" ("field1", "field2") VALUES (\'value1\', \'value2\')');

        });

        it("throws an exception if the `INTO` clause is missing", function() {

            $closure = function() {
                $this->insert
                     ->values(['field1' => 'value1', 'field2' => 'value2'])
                     ->toString();
            };
            expect($closure)->toThrow(new SqlException("Invalid `INSERT` statement, missing `INTO` clause."));

        });

    });

    describe("->values()", function() {

        it("inserts in batch", function() {

            $this->insert->into('table')->values([
                'field1' => 'value1',
                'field2' => 'value2'
            ])->values([
                'field1' => 'value3',
                'field2' => 'value4'
            ]);
            expect($this->insert->toString())->toBe('INSERT INTO "table" ("field1", "field2") VALUES (\'value1\', \'value2\'), (\'value3\', \'value4\')');

        });

        it("assures the custom casting handler is correctly called if set", function() {

            $getType = function($field){};

            $caster = function($value, $states) use ($getType) {
              expect($states['name'])->toBe('field');
              expect($states['schema'])->toBe($getType);
              expect($value)->toBe('value');
              return "'casted'";
            };

            $this->dialect->caster($caster);
            $insert = $this->dialect->statement('insert', ['schema' => $getType]);
            $insert->into('table')->values(['field' => 'value']);

            expect($insert->toString())->toBe('INSERT INTO "table" ("field") VALUES (\'casted\')');

        });

    });

    describe("->__toString()" , function() {

        it("casts object to string query", function() {

            $this->insert->into('table')->values(['field' => 'value']);
            $query = 'INSERT INTO "table" ("field") VALUES (\'value\')';
            expect($this->insert)->not->toBe($query);
            expect((string) $this->insert)->toBe($query);
            expect("{$this->insert}")->toBe($query);

        });

    });

});