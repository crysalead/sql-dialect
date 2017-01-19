<?php
namespace Lead\Sql\Dialect\Spec\Suite\Statement;

use Lead\Sql\Dialect\SqlException;
use Lead\Sql\Dialect\Dialect;

use Kahlan\Plugin\Stub;

describe("Truncate", function() {

    beforeEach(function() {
        $this->dialect = new Dialect();
        $this->truncate = $this->dialect->statement('truncate');
    });

    describe("->table()", function() {

        it("sets the `TABLE` clause", function() {

            $this->truncate->table('table');
            expect($this->truncate->toString())->toBe('TRUNCATE TABLE "table"');

        });

        it("throws an exception if the `TABLE` clause is missing", function() {

            $closure = function() {
                $this->truncate->toString();
            };
            expect($closure)->toThrow(new SqlException("Invalid `TRUNCATE` statement, missing `TABLE` clause."));

        });

    });

});