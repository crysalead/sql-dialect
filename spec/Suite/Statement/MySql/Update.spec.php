<?php
namespace Lead\Sql\Dialect\Spec\Suite\Statement\MySql;

use Lead\Sql\Dialect\Dialect\MySql;

describe("MySql Update", function() {

    beforeEach(function() {
        $this->dialect = new MySql();
        $this->update = $this->dialect->statement('update');
    });

    describe("->lowPriority()", function() {

        it("sets the `LOW_PRIORITY` flag", function() {

            $this->update
                ->lowPriority()
                ->table('table')
                ->values(['field' => 'value']);

            expect($this->update->toString())->toBe('UPDATE LOW_PRIORITY `table` SET `field` = \'value\'');

        });

    });

    describe("->ignore()", function() {

        it("sets the `IGNORE` flag", function() {

            $this->update
                ->ignore()
                ->table('table')
                ->values(['field' => 'value']);

            expect($this->update->toString())->toBe('UPDATE IGNORE `table` SET `field` = \'value\'');

        });

    });

});
