<?php

/**
 * Page the inner iterator output with provided offset and limit.
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Filtered.php
 * Source Code
 */
namespace CrudeForum\CrudeForum\Iterator;

use \Iterator;

/**
 * Page the inner iterator output with provided offset and limit.
 *
 * @category Interface
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/Filtered.php
 * Source Code
 */
class Paged implements Wrapper, Iterator
{

    use ProxyTrait;
    use WrapperTrait;

    private $_offset = 0;
    private $_limit = -1;
    private $_pagePos = 0;

    /**
     * Class constructor
     *
     * @param integer $offset Offset for retrieving values in inner iterator.
     * @param integer $limit  Maximum number of values to retrieve in inner iterator.
     */
    public function __construct(int $offset=0, int $limit=-1)
    {
        $this->_offset = $offset;
        $this->_limit = $limit;
    }

    /**
     * Implements Iterator method
     *
     * @inheritDoc
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->iter()->rewind(); // rewind first
        for ($i=0; $this->iter()->valid() && ($i < $this->_offset); $i++) {
            $this->iter()->next(); // skip through items
        }
        $this->_pagePos = 0;
    }

    /**
     * Implements Iterator method
     *
     * @inheritDoc
     *
     * @return void
     */
    public function next(): void
    {
        $this->iter()->next();
        $this->_pagePos += 1;
    }

    /**
     * Implements Iterator method
     *
     * @inheritDoc
     *
     * @return bool
     */
    public function valid(): bool
    {
        if ($this->_limit === -1) return $this->iter()->valid();
        return ($this->_pagePos >= $this->_limit) ?
            false : $this->iter()->valid();
    }
}
