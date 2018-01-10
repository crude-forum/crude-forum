<?php

/**
 * Error to throw if attempt to open a non-existed post
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum Source Code
 */

namespace CrudeForum\CrudeForum\Exception;

use \Exception;

/**
 * Error to throw if the post is corrupted or misformatted.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum Source Code
 */
class PostInvalid extends Exception
{
    /**
     * Class constructor
     *
     * @param string $message The exception message
     */
    public function __construct(string $message='post invalid')
    {
        parent::__construct($message);
    }
}