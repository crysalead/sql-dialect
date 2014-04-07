<?php
namespace sql\spec\suite\statement\mysql;

use sql\dialect\PostgreSql;

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