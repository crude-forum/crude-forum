<?php

namespace ywsing\CrudeForum;

class ForumIndex extends IteratorWrapper implements \Iterator {
    public function current() {
        return PostSummary::fromIndexLine(
            $this->iter->current(),
            $this->iter->key());
    }
}
