<?php

use CrudeForum\CrudeForum\Iterator\Paged;
use CrudeForum\CrudeForum\Iterator\Filtered;
use CrudeForum\CrudeForum\Iterator\Wrapper;

describe('CrudeForum\CrudeForum\Iterator\Paged', function () {
    it('implements Iterator', function () {
        $paged = new Paged();
        expect($paged instanceof \Iterator)->toBeTruthy();
    });
    it('implements CrudeForum\CrudeForum\Iterator\Wrapper', function () {
        $paged = new Paged();
        expect($paged instanceof Wrapper)->toBeTruthy();
    });
    it('offset works as expected', function () {
        $list = (new Paged(1))->wrap((new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])));
        $arr = iterator_to_array($list);
        expect(sizeof($arr))->toBe(9);
        expect(array_shift($arr))->toBe(2);
        expect(array_shift($arr))->toBe(3);
        expect(array_shift($arr))->toBe(4);
    });
    it('limit works as expected', function () {
        $list = (new Paged(0, 5))->wrap((new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])));
        $arr = iterator_to_array($list);
        expect(sizeof($arr))->toBe(5);

        $list = (new Paged(0, 12))->wrap((new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])));
        $arr = iterator_to_array($list);
        expect(sizeof($arr))->toBe(10);
    });
    it('offset and limit works together as expected', function () {
        $list = (new Paged(1, 5))->wrap((new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])));
        $arr = iterator_to_array($list);
        expect(sizeof($arr))->toBe(5);

        $list = (new Paged(1, 12))->wrap((new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])));
        $arr = iterator_to_array($list);
        expect(sizeof($arr))->toBe(9);
    });
    it('be used with CrudeForum\CrudeForum\Iterator\Filtered in expected way', function () {

        // test 1: page with only offset
        $list = (new Filtered(
            function (int $int) {
                return ($int % 2) == 1;
            }
        ))->wrap((new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])));
        $list = (new Paged(2))->wrap($list);

        $arr = iterator_to_array($list);
        expect(sizeof($arr))->toBe(4);
        expect(array_shift($arr))->toBe(5);
        expect(array_shift($arr))->toBe(7);
        expect(array_shift($arr))->toBe(9);
        expect(array_shift($arr))->toBe(11);

        // test 2: page with both limit and offset
        $list = (new Filtered(
            function (int $int) {
                return ($int % 2) == 1;
            }
        ))->wrap((new ArrayIterator([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])));
        $list = (new Paged(2, 2))->wrap($list);

        $arr = iterator_to_array($list);
        expect(sizeof($arr))->toBe(2);
        expect(array_shift($arr))->toBe(5);
        expect(array_shift($arr))->toBe(7);
    });
});
