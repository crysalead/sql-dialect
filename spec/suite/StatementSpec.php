<?php
namespace sql\spec\suite;

use sql\SqlException;
use sql\Statement;
use kahlan\plugin\Stub;

describe("Statement", function() {

    beforeEach(function() {
        $this->statement = new Statement();
    });

    describe("->dialect()", function() {

        it("gets/sets a dialect", function() {

            $dialect = Stub::create();
            $this->statement->dialect($dialect);
            expect($this->statement->dialect())->toBe($dialect);

        });

        it("throws an exception if no dialect has been defined", function() {

            $closure = function() {
                $this->statement->dialect();
            };
            expect($closure)->toThrow(new SqlException("Missing SQL dialect adapter."));

        });

    });

    describe("->data()", function() {

        it("gets/sets some data", function() {

            $this->statement->data('key', 'value');
            expect($this->statement->data('key'))->toBe('value');

        });

    });

    describe("->setFlag()/->getFlag()", function() {

        it("gets/sets some flag", function() {

            expect($this->statement->setFlag('flag'))->toBe(true);
            expect($this->statement->getFlag('flag'))->toBe(true);
            expect($this->statement->setFlag('flag', false))->toBe(false);
            expect($this->statement->getFlag('flag'))->toBe(false);

        });

    });

    describe("->__call()", function() {

        it("throws an exception on undefined method call", function() {

            $closure = function() {
                $this->statement->undefined();
            };
            expect($closure)->toThrow(new SqlException("Invalid clause `undefined` for `sql\\Statement`."));

        });

    });

});