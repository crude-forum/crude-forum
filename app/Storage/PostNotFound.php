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

namespace CrudeForum\CrudeForum\Storage;

use \Exception;

/**
 * Error to throw if attempt to open a non-existed post
 *
 * @category Class
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum Source Code
 */
class PostNotFound extends Exception
{
    private $_postID;

    /**
     * Class constructor
     *
     * @param string $postID The postID of the post that is not found.
     */
    public function __construct(string $postID)
    {
        parent::__construct("Exception: post not found (postID={$postID})");
        $this->_postID = $postID;
    }

    /**
     * Retrieve the postID from error.
     *
     * @return string postID.
     */
    public function getPostID(): string
    {
        return $this->_postID;
    }
}