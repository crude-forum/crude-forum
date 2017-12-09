<?php

namespace ywsing\CrudeForum;

class Paged extends IteratorWrapper implements \Iterator {

    public $iter = 0;
    private $offset = 0;
    private $limit = -1;

    public function __construct(\Iterator $iter, int $offset=0, int $limit=-1) {
        $this->iter = $iter;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function rewind() {
        $this->iter->rewind(); // rewind first
        for ($i=0; $this->iter->valid() && ($i < $this->offset); $i++) {
            $this->iter->next(); // skip through items
        }
    }

    public function valid() {
        if ($this->limit === -1) return $this->iter->valid();
        if ($this->iter->key() > $this->offset + $this->limit - 1) return FALSE;
        return $this->iter->valid();
    }
}
