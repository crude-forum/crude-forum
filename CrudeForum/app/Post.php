<?php

namespace ywsing\CrudeForum;

class Post {
    public $title = '';
    public $body = '';
    public function __construct($title='', $body='') {
        $this->title = (string) $title;
        $this->body = (string) $body;
    }
}
