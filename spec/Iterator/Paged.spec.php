<?php

use CrudeForum\CrudeForum\Iterator\Paged;
use CrudeForum\CrudeForum\Iterator\Wrapper;
use \Iterator;

describe('CrudeForum\CrudeForum\Iterator\Paged', function () {
    it('implements Iterator', function () {
        $paged = new Paged();
        expect($paged instanceof Iterator)->toBeTruthy();
    });
    it('implements CrudeForum\CrudeForum\Iterator\Wrapper', function () {
        $paged = new Paged();
        expect($paged instanceof Wrapper)->toBeTruthy();
    });
});
