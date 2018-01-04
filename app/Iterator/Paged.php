<?php

namespace CrudeForum\CrudeForum\Iterator;

class Paged implements \Iterator, Wrapper
{

    use ProxyTrait;
    use WrapperTrait;

    public $iter = 0;
    private $_offset = 0;
    private $_limit = -1;

    public function __construct(int $offset=0, int $limit=-1)
    {
        $this->_offset = $offset;
        $this->_limit = $limit;
    }

    public function rewind()
    {
        $this->iter->rewind(); // rewind first
        for ($i=0; $this->iter->valid() && ($i < $this->_offset); $i++) {
            $this->iter->next(); // skip through items
        }
    }

    public function valid()
    {
        if ($this->_limit === -1) return $this->iter->valid();
        if ($this->iter->key() > $this->_offset + $this->_limit - 1) return false;
        return $this->iter->valid();
    }
}
