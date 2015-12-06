<?php
namespace Lead\Sql\Spec\Suite\Statement\PostgreSql;

use Lead\Sql\Dialect\PostgreSql;

describe("PostgreSql Insert", function() {

    beforeEach(function() {
        $this->dialect = new PostgreSql();
        $this->insert = $this->dialect->statement('insert');
    });

    describe("->returning()", function() {

        it("sets `RETURNING`", function() {

            $this->insert
                ->into('table')
                ->values(['field' => 'value'])
                ->returning('*');

            expect($this->insert->toString())->toBe('INSERT INTO "table" ("field") VALUES (\'value\') RETURNING *');

        });

    });

});