<?php

use CrudeForum\CrudeForum\Iterator\Filtered;
use CrudeForum\CrudeForum\Iterator\Wrapper;

describe('CrudeForum\CrudeForum\Iterator\Filtered', function () {
    it('implements Iterator', function () {
        $filtered = new Filtered(function () {});
        expect($filtered instanceof \Iterator)->toBeTruthy();
    });
    it('implements CrudeForum\CrudeForum\Iterator\Wrapper', function () {
        $filtered = new Filtered(function () {});
        expect($filtered instanceof Wrapper)->toBeTruthy();
    });
    it('only returns filtered item', function () {
        $list = (new Filtered(
            function (int $int) {
                return ($int % 2) == 1;
            }
        ))->wrap((new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])));
        foreach ($list as $int) {
            expect(in_array($int, [1, 3, 5, 7, 9, 11]))->toBeTruthy();
        }
    });
});
