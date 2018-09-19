<?php
namespace Lead\Sql\Dialect\Spec\Suite\Statement\Sqlite;

use Lead\Sql\Dialect\SqlException;
use Lead\Sql\Dialect\Dialect\Sqlite;

use Kahlan\Plugin\Stub;

describe("Sqlite Truncate", function() {

    beforeEach(function() {
        $this->dialect = new Sqlite();
        $this->truncate = $this->dialect->statement('truncate');
    });

    describe("->table()", function() {

        it("sets the `TABLE` clause", function() {

            $this->truncate->table('table');
            expect($this->truncate->toString())->toBe('DELETE FROM "table";DELETE FROM "SQLITE_SEQUENCE" WHERE name="table"');

        });

        it("throws an exception if the `TABLE` clause is missing", function() {

            $closure = function() {
                $this->truncate->toString();
            };
            expect($closure)->toThrow(new SqlException("Invalid `TRUNCATE` statement, missing `TABLE` clause."));

        });

    });

});