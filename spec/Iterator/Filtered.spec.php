<?php

use CrudeForum\CrudeForum\Iterator\Filtered;
use CrudeForum\CrudeForum\Iterator\Wrapper;
use \Iterator;

describe('CrudeForum\CrudeForum\Iterator\Filtered', function () {
    it('implements Iterator', function () {
        $filtered = new Filtered(function () {});
        expect($filtered instanceof Iterator)->toBeTruthy();
    });
    it('implements CrudeForum\CrudeForum\Iterator\Wrapper', function () {
        $filtered = new Filtered(function () {});
        expect($filtered instanceof Wrapper)->toBeTruthy();
    });
});
