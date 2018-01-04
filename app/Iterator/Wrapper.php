<?php

/**
 * Abstract Iterator wrapper class.
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
  * Abstract Iterator wrapper class.
  *
  * @category Interface
  * @package  CrudeForum\CrudeForum\Iterator
  * @author   Koala Yeung <koalay@gmail.com>
  * @license  https://opensource.org/licenses/MIT MIT License
  * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Wrapper.php
  * Source Code
  */
interface Wrapper extends Iterator
{
    /**
     * To wrap an Iterator as a Wrapper and return the Wrapper.
     * Expect to use as a middleware layer to modify the behaviour
     * of an Interator as another one.
     *
     * @param Iterator $iter An iterator to wrap with.
     *
     * @return Wrapper
     */
    public function wrap(Iterator $iter): Wrapper;
}