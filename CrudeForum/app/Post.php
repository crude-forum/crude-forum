<?php

namespace ywsing\CrudeForum;

class Post {
    public $title = '';
    public $body = '';
    public $noAutoBr = FALSE;

    public function __construct($title='', $body='', $noAutoBr=FALSE) {
        $this->title = (string) $title;
        $this->body = (string) $body;
        $this->noAutoBr = (bool) $noAutoBr;
    }

    public function saveBody() {
        return htmlspecialchars($this->body);
    }

    public function htmlBody() {
        return nl2br($this->body);
    }
}
