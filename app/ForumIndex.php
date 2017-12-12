<?php

namespace ywsing\CrudeForum;

use \ywsing\CrudeForum\Iterator\Proxy;

class ForumIndex implements \Iterator {
    use Proxy;
    public function current() {
        return PostSummary::fromIndexLine(
            trim($this->iter->current()),
            $this->iter->key());
    }
}
