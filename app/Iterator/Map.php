<?php

/**
 * Map each iteration output of inner iterator with the
 * provided callable.
 *
 * PHP Version 8.1
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
 * Map each iteration output of inner iterator with the
 * provided callable.
 *
 * @category Interface
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Filtered.php
 * Source Code
 */
class Map implements Wrapper, Iterator {

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
     * Pass on current() to inner iterator
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return call_user_func($this->_callback, $this->_iter->current());
    }
}
