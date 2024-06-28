<?php

/**
 * Iterator for PostSummary wrapping an index lines iterator.
 *
 * PHP Version 8.0
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
use \CrudeForum\CrudeForum\Iterator\WrapperTrait;

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
    use WrapperTrait;

    /**
     * Returns the current line as PostSummary
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return PostSummary::fromIndexLine(
            trim((string) $this->iter()->current()),
            $this->iter()->key()
        );
    }
}
