<?php
namespace Lead\Sql\Dialect\Spec\Suite\Statement\PostgreSql;

use Lead\Sql\Dialect\Dialect\PostgreSql;

describe("PostgreSql Update", function() {

    beforeEach(function() {
        $this->dialect = new PostgreSql();
        $this->update = $this->dialect->statement('update');
    });

    describe("->returning()", function() {

        it("sets `RETURNING`", function() {

            $this->update
                ->table('table')
                ->values(['field' => 'value'])
                ->returning('*');

            expect($this->update->toString())->toBe('UPDATE "table" SET "field" = \'value\' RETURNING *');

        });

    });

});