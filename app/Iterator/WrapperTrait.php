<?php

/**
 * Trait implementation of Wrapper.
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
use \Iterator;

 /**
  * Trait implementation of Wrapper.
  *
  * @category Trait
  * @package  CrudeForum\CrudeForum\Iterator
  * @author   Koala Yeung <koalay@gmail.com>
  * @license  https://opensource.org/licenses/MIT MIT License
  * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Wrapper.php
  * Source Code
  */
trait WrapperTrait
{

    /**
     * An abstract method to access the wrapped iterator
     *
     * @return Iterator
     */
    abstract function &iter(): Iterator;

    /**
     * To wrap an Iterator into the current object.
     *
     * @param Iterator $iter An iterator to wrap with.
     *
     * @return Wrapper
     */
    public function wrap(Iterator $iter): Wrapper
    {
        $this->_iter = $iter;
        return $this;
    }
}