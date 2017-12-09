<?php

namespace ywsing\CrudeForum;

class ForumIndex implements \Iterator {

    private $iter = 0;

    public function __construct(\Iterator $iter) {
        $this->iter = $iter;
    }

    public function  __destruct() {
        unset($this->iter);
    }

    public function current() {
        return PostSummary::fromIndexLine(
            $this->iter->current(), $this->iter->key());
    }

    public function key() {
        return $this->iter->key();
    }

    public function next() {
        return $this->iter->next();
    }

    public function rewind() {
        return $this->iter->rewind();
    }

    public function valid() {
        return $this->iter->valid();
    }
}
