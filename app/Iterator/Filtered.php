<?php

namespace CrudeForum\CrudeForum\Iterator;

class Filtered implements \Iterator, Wrapper
{

    use ProxyTrait;
    use FilterTrait;

    private $_callback;

    public function __construct(callable $callback)
    {
        $this->_callback = $callback;
    }

    private function _tryUntilPass()
    {
        while ($this->iter->valid() && !call_user_func($this->_callback, $this->iter->current())) {
            // if not pass the callback,
            // skip to the next one
            $this->iter->next();
        }
    }

    public function rewind()
    {
        $this->iter->rewind();
        $this->_tryUntilPass();
    }

    public function next()
    {
        $this->iter->next();
        $this->_tryUntilPass();
    }
}
