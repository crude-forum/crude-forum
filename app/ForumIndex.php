<?php

namespace ywsing\CrudeForum;

use \ywsing\CrudeForum\Iterator\Wrapper;

class ForumIndex extends Wrapper implements \Iterator {
    public function current() {
        return PostSummary::fromIndexLine(
            trim($this->iter->current()),
            $this->iter->key());
    }
}
