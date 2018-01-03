<?php

namespace CrudeForum\CrudeForum;

use \CrudeForum\CrudeForum\Iterator\Proxy;

class ForumIndex implements \Iterator {
    use Proxy;
    public function current() {
        return PostSummary::fromIndexLine(
            trim($this->iter->current()),
            $this->iter->key());
    }
}
