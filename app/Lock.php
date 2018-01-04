<?php

/**
 * Abstraction of forum locking mechanism
 *
 * PHP Version 7.1
 *
 * @file     FileStorage.php
 * @category File
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */

namespace CrudeForum\CrudeForum;

/**
 * Abstraction of forum locking mechanism
 *
 * @category Interface
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */
interface Lock
{
    /**
     * Unlock the lock
     *
     * @return boolean True if the unlock is successful. False otherwise.
     */
    public function unlock(): bool;
}