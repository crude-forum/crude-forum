<?php

/**
 * Utility functions collection.
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
use \Exception;

 /**
  * Utility functions collection.
  *
  * @category Class
  * @package  CrudeForum\CrudeForum\Iterator
  * @author   Koala Yeung <koalay@gmail.com>
  * @license  https://opensource.org/licenses/MIT MIT License
  * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Wrapper.php
  * Source Code
  */
class Utils
{
    /**
     * Chains wrappers into a single wrapper.
     *
     * @param Wrapper ...$wrappers Wrappers to apply to an Iterator
     *                             from inner to outer.
     *
     * @return WrapperChain
     */
    public static function chainWrappers(
        Wrapper ...$wrappers
    ): WrapperChain {
        return new WrapperChain(...$wrappers);
    }
}