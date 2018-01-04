<?php

/**
 * Iterator for PostSummary wrapping an index lines iterator.
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/ForumIndex.php
 * Source Code
 */
namespace CrudeForum\CrudeForum;

use \CrudeForum\CrudeForum\Iterator\ProxyTrait;

/**
 * Iterator for PostSummary wrapping an index lines iterator.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum
 * @author   Koala <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/ForumIndex.php
 * Source Code
 */
class ForumIndex implements \Iterator
{
    use ProxyTrait;

    /**
     * Returns the current line as PostSummary
     *
     * @return void
     */
    public function current()
    {
        return PostSummary::fromIndexLine(
            trim($this->iter->current()),
            $this->iter->key()
        );
    }
}
