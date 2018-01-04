<?php

/**
 * Trait implementation of \Iterator
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Wrapper.php
 * Source Code
 */
namespace CrudeForum\CrudeForum\Iterator;

 /**
  * Trait implementation of \Iterator
  *
  * @category Trait
  * @package  CrudeForum\CrudeForum\Iterator
  * @author   Koala Yeung <koalay@gmail.com>
  * @license  https://opensource.org/licenses/MIT MIT License
  * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Wrapper.php
  * Source Code
  */
trait ProxyTrait
{

    public $iter = null;

    /**
     * Class constructor.
     *
     * @param \Iterator $iter inner iterator.
     *
     * @return void
     */
    public function __construct(\Iterator $iter)
    {
        $this->iter = $iter;
    }

    /**
     * Class destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        unset($this->iter);
    }

    /**
     * Pass on current() to inner iterator
     *
     * @return void
     */
    public function current()
    {
        return $this->iter->current();
    }

    /**
     * Pass on key() to inner iterator
     *
     * @return void
     */
    public function key()
    {
        return $this->iter->key();
    }

    /**
     * Pass on next() to inner iterator
     *
     * @return void
     */
    public function next()
    {
        return $this->iter->next();
    }

    /**
     * Pass on rewind() to inner iterator
     *
     * @return void
     */
    public function rewind()
    {
        return $this->iter->rewind();
    }

    /**
     * Pass on valid() to inner iterator
     *
     * @return void
     */
    public function valid()
    {
        return $this->iter->valid();
    }
}
