<?php

namespace CrudeForum\CrudeForum\Iterator;

class FileObject implements \Iterator {

    private $position = 0;
    private $fh = FALSE;
    private $buffer = FALSE;

    public function __construct($fh) {
        if (!is_resource($fh))
            throw new \InvalidArgumentException('not a valid resource');
        $this->fh = $fh;
        $this->read();
    }

    public function  __destruct() {
        if ($this->fh !== FALSE) fclose($this->fh);
        $this->fh = FALSE;
    }

    private function read() {
        // read until the line is not empty,
        // or has reached end of file
        while (($this->buffer = fgets($this->fh)) !== FALSE)
            if (!empty(trim($this->buffer))) break;
        return $this->buffer;
    }

    public function current() {
        if ($this->fh === FALSE)
            throw new \Exception('file has already closed');
        return $this->buffer;
    }

    public function key() {
        if ($this->fh === FALSE)
            throw new \Exception('file has already closed');
        return $this->position;
    }

    public function next() {
        if ($this->fh === FALSE)
            throw new \Exception('file has already closed');
        $this->position++;
        $this->read(); // read 1 line into buffer
    }

    public function rewind() {
        if ($this->fh === FALSE)
            throw new \Exception('file has already closed');
        rewind($this->fh);
        $this->read(); // read first line into buffer
    }

    public function valid() {
        if ($this->fh === FALSE)
            throw new \Exception('file has already closed');
        return ($this->buffer !== FALSE);
    }
}
