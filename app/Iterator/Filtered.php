<?php

/**
 * Filtering each iteration output of inner iterator with the
 * provided callable.
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Filtered.php
 * Source Code
 */
namespace CrudeForum\CrudeForum\Iterator;

use \Iterator;

/**
 * Filtering each iteration output of inner iterator with the
 * provided callable.
 *
 * @category Interface
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Filtered.php
 * Source Code
 */
class Filtered implements Wrapper, Iterator
{

    use ProxyTrait;
    use WrapperTrait;

    private $_callback;

    /**
     * Class constructor
     *
     * @param callable $callback The callable to filter iteration outputs with.
     */
    public function __construct(callable $callback)
    {
        $this->_callback = $callback;
    }

    /**
     * To run through the `current()` returns of inner Iterator
     * until the callback return a `true`. Use `next()` to get
     * to the next iteration if the callback returned a `false`.
     *
     * @internal
     *
     * @return void
     */
    private function _tryUntilPass()
    {
        while ($this->iter()->valid() && !call_user_func($this->_callback, $this->iter()->current())) {
            // if not pass the callback,
            // skip to the next one
            $this->iter()->next();
        }
    }

    /**
     * Implements Iterator method
     *
     * @inheritDoc
     *
     * @uses Filtered::_tryUntilPass() to skip through invalid items
     * in the beginning.
     *
     * @return void
     */
    public function rewind()
    {
        $this->iter()->rewind();
        $this->_tryUntilPass();
    }

    /**
     * Implements Iterator method
     *
     * @inheritDoc
     *
     * @uses Filtered::_tryUntilPass() to skip through invalid items
     * after the next one.
     *
     * @return void
     */
    public function next()
    {
        $this->iter()->next();
        $this->_tryUntilPass();
    }
}
