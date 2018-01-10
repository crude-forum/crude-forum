<?php

use CrudeForum\CrudeForum\Iterator\Wrapper;
use CrudeForum\CrudeForum\Iterator\WrapperTrait;
use CrudeForum\CrudeForum\Iterator\ProxyTrait;
use CrudeForum\CrudeForum\Iterator\Utils;
use \Iterator as Iterator;

class TestWrapper implements Wrapper, Iterator
{
    use ProxyTrait;
    use WrapperTrait;

    private $_name = '';

    public function __construct(string $name)
    {
        $this->_name = $name;
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function getStack(): string
    {
        if (($inner = $this->iter()) === null)
        {
            return $this->_name . '->null';
        }
        $innerName = ($inner instanceof TestWrapper) ? $inner->getStack() : get_class($inner);
        return $this->_name . '->' . $innerName;
    }

    public function current(): string
    {
        $stack = $this->getStack();
        //echo "called {$stack}::current()\n";
        return $this->_name . ': ' . $this->iter()->current();
    }
}

describe('CrudeForum\CrudeForum\Iterator\Wrapper', function () {
    describe('::wrap', function () {
        it('wrapped iterator uses the outer class method', function () {

            // prepare object to test with
            $obj0 = new ArrayIterator(['hello', 'world']);
            $obj1 = (new TestWrapper('wrapper1'))->wrap($obj0);
            $obj2 = (new TestWrapper('wrapper2'))->wrap($obj1);
            $obj3 = (new TestWrapper('wrapper3'))->wrap($obj2);
            $objects = [$obj1, $obj2, $obj3];

            $expectedPrefix = '';
            foreach ($objects as $objkey => $obj) {
                $expectedPrefix = 'wrapper' . ($objkey + 1) . ': ' . $expectedPrefix;
                foreach ($obj as $value) {
                    expect($value)->toMatch('/^' . preg_quote($expectedPrefix) .  '/');
                }
            }
        });
    });
});

describe('CrudeForum\CrudeForum\Iterator\Utils', function () {
    describe('::chainWrappers', function () {
        it('wrapped iterator uses the outer class method', function () {

            // prepare object to test with
            $obj0 = new ArrayIterator(['hello', 'world']);
            $obj1 = Utils::chainWrappers(
                new TestWrapper('wrapper1')
            )->wrap($obj0);
            $obj2 = Utils::chainWrappers(
                new TestWrapper('wrapper1'),
                new TestWrapper('wrapper2')
            )->wrap($obj0);
            $obj3 = Utils::chainWrappers(
                new TestWrapper('wrapper1'),
                new TestWrapper('wrapper2'),
                new TestWrapper('wrapper3')
            )->wrap($obj0);
            $objects = [$obj1, $obj2, $obj3];

            $expectedPrefix = '';
            foreach ($objects as $objkey => $obj) {
                $expectedPrefix = 'wrapper' . ($objkey + 1) . ': ' . $expectedPrefix;
                foreach ($obj as $value) {
                    expect($value)->toMatch('/^' . preg_quote($expectedPrefix) .  '/');
                }
            }
        });
    });
});
