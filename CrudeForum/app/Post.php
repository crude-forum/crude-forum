<?php

namespace ywsing\CrudeForum;

class Post {

    public $title = '';
    public $body = '';

    public function __construct($title='', $body='') {
        $this->title = (string) $title;
        $this->body = (string) $body;
    }

    public static function fromFile($fh): Post {
        $title = '';
        $body  = '';
        if (is_resource($fh)) {
            $title = fgets($fh, 4096);
            $lineBreak = '<br>';
            while(!feof($fh)) {
                $line = fgets($fh, 4096);
                if (is_string(strstr($line, 'NO-LINE-END-BR'))) $lineBreak = '';
                else $body .= $line . $lineBreak;
            }
            return new Post($title, $body);
        } else {
            throw new InvalidArgumentException('not a valid resource');
        }
    }
}
