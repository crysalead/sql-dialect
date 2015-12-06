<?php
namespace Lead\Sql\Spec\Suite\Statement\PostgreSql;

use Lead\Sql\Dialect\PostgreSql;

describe("PostgreSql Delete", function() {

    beforeEach(function() {
        $this->dialect = new PostgreSql();
        $this->delete = $this->dialect->statement('delete');
    });

    describe("->returning()", function() {

        it("sets `RETURNING`", function() {

            $this->delete->from('table')->returning('*');
            expect($this->delete->toString())->toBe('DELETE FROM "table" RETURNING *');

        });

    });

});