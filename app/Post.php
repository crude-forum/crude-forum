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

    public static function fromText(string $text): ?Post {
        if (empty($text)) throw new \InvalidArgumentException('expected non-empty string as parameter');
        $lines = explode("\n", str_replace("\r\n", "\n", $text), 3);
        $size = sizeof($lines);
        if ($size === 1) {
            throw new \Exception('the post is misformatted');
            return NULL;
        } else if ($size === 2) {
            throw new \Exception('the post is missing a proper body');
            return NULL;
        }
        return new Post($lines[0], $lines[2]);
    }

    public static function replyFor(?Post $parent): Post {
        if ($parent === NULL) return new Post();
        if (empty($parent->title) && empty($parent->body)) return new Post();

        // generate reply post
        $title = (strpos($parent->title, 'Re: ') === 0) ?
            $parent->title : 'Re: ' . $parent->title;
        $body = $parent->filterBody(function ($line) {
            return "|$line";
        }) . "\n\n";
        return new Post($title, $body);
    }

    public function safeTitle(): string {
        return htmlspecialchars($this->title);
    }

    public function filterBody(callable $lineCallback) {
        $lines = array_map(
            $lineCallback,
            explode("\n", trim($this->body)));
        return implode("\n", $lines);
    }

    public function safeBody(): string {
        return htmlspecialchars($this->body);
    }

    public function htmlBody(): string {
        return nl2br($this->body);
    }
}
