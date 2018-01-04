<?php

/**
 * A standalone Iterator implementation to read files. Will
 * close the file resource when the object is unset / destructed.
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/FileObject.php
 * Source Code
 */
namespace CrudeForum\CrudeForum\Iterator;
use \Iterator;

/**
 * A standalone Iterator implementation to read files. Will
 * close the file resource when the object is unset / destructed.
 *
 * @category Interface
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Iterator/FileObject.php
 * Source Code
 */

class FileObject implements Iterator
{

    private $_position = 0;
    private $_fh = false;
    private $_buffer = false;


    /**
     * Class constructor.
     *
     * @param resource $fh The file handler of the file.
     */
    public function __construct($fh)
    {
        if (!is_resource($fh))
            throw new \InvalidArgumentException('not a valid resource');
        $this->_fh = $fh;
        $this->_read();
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->_fh !== false) fclose($this->_fh);
        $this->_fh = false;
    }

    /**
     * Read a line from the file and trim it.
     *
     * @return mixed
     */
    private function _read()
    {
        // read until the line is not empty,
        // or has reached end of file
        while (($this->_buffer = fgets($this->_fh)) !== false)
            if (!empty(trim($this->_buffer))) break;
        return $this->_buffer;
    }

    /**
     * Implements Iterator::current
     *
     * @inheritDoc
     *
     * @return mixed
     */
    public function current()
    {
        if ($this->_fh === false)
            throw new \Exception('file has already closed');
        return $this->_buffer;
    }

    /**
     * Implements Iterator::key
     *
     * @inheritDoc
     *
     * @return int
     */
    public function key(): int
    {
        if ($this->_fh === false)
            throw new \Exception('file has already closed');
        return $this->_position;
    }

    /**
     * Implements Iterator::next
     *
     * Increment the position pointer and read a line into buffer.
     *
     * @inheritDoc
     *
     * @uses FileObject::_read to read line into buffer.
     *
     * @return void
     */
    public function next()
    {
        if ($this->_fh === false)
            throw new \Exception('file has already closed');
        $this->_position++;
        $this->_read(); // read 1 line into buffer
    }

    /**
     * Implements Iterator::rewind
     *
     * Rewind the file pointer to the beginning of the file.
     *
     * @inheritDoc
     *
     * @uses FileObject::_read to read first line into buffer.
     *
     * @return void
     */
    public function rewind()
    {
        if ($this->_fh === false)
            throw new \Exception('file has already closed');
        $this->_position = 0;
        rewind($this->_fh);
        $this->_read(); // read first line into buffer
    }

    /**
     * Implements Iterator::valid
     *
     * See if there is valid value in the read buffer.
     *
     * @inheritDoc
     *
     * @return void
     */
    public function valid()
    {
        if ($this->_fh === false)
            throw new \Exception('file has already closed');
        return ($this->_buffer !== false);
    }
}
