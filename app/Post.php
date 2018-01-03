<?php

namespace CrudeForum\CrudeForum;

define('POST_HEADER_NAMES', [
    'author' => '來自',
    'time' => '時間',
]);
define('POST_HEADER_NAMES_LOOPUP',
    array_flip(POST_HEADER_NAMES));

class Post {
    public $title = '';
    public $body = '';
    public $header = []; // author, time information (meta data)
    public $noAutoBr = FALSE;

    public function __construct($title='', $body='', $header=[], $noAutoBr=FALSE) {
        $this->title = (string) $title;
        $this->body = (string) $body;
        $this->header = (array) $header;
        $this->noAutoBr = (bool) $noAutoBr;
    }

    public static function headerName(string $humanName): string {
        return POST_HEADER_NAMES_LOOPUP[$humanName] ?? $humanName;
    }

    public static function headerHumanName(string $name): string {
        return POST_HEADER_NAMES[$name] ?? $name;
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

        // parse header
        $lineNum = -1;
        $title = $lines[0];
        $header = [];
        $bodyLines = explode("\n", $lines[2]);
        foreach ($bodyLines as $lineNum => $line) {
            // identify any colon (either ASCII unicode, in the line
            if (preg_match("/^(.+?)(:|\x{FE30}|\x{FF1A})(.+?)$/u", $line, $matches)) {
                // $header["machine_name"] = "header_value"
                $header[Post::headerName($matches[1])] = trim($matches[3]);
            } else if (empty(trim($line))) {
                // header ended
                break;
            } else {
                // throw error here
                throw \Exception('the post is mis-formatted');
                return NULL;
            }
        }
        if ($lineNum < 0) throw new \Exception('the post body has no header');

        $body = implode("\n", array_splice($bodyLines, $lineNum + 1));
        return new Post($title, $body, $header);
    }

    public static function replyFor(?Post $parent): Post {
        if ($parent === NULL) return new Post();
        if (empty($parent->title) && empty($parent->body)) return new Post();

        // generate reply post
        $title = (strpos($parent->title, 'Re: ') === 0) ?
            $parent->title : 'Re: ' . $parent->title;

        $author = $parent->header['author'] ?? '';
        $authorHeaderName = Post::headerHumanName('author');
        $time = $parent->header['time'] ?? '';
        $timeHeaderName = Post::headerHumanName('time');

        // generate quote to replying post
        $quoteLine = function ($line) {
            return "| $line";
        };
        $header = implode("\n", array_map(
            $quoteLine,
            [
                "{$authorHeaderName}：{$author}",
                "{$timeHeaderName}：{$time}",
            ]));
        $mainBody = $parent->filterBody($quoteLine);
        $body = "{$header}\n|\n{$mainBody}\n\n";

        return new Post($title, $body);
    }

    public function safeTitle(): string {
        return htmlspecialchars($this->title);
    }

    public function storageHeader(): array {
        // encode all header values
        $header = $this->header;
        return array_map(function ($key) use ($header) {
            $name = Post::headerHumanName($key);
            $value = $header[$key];
            return "{$name}：{$value}";
        }, array_keys($header));
    }

    public function safeHeader(): string {
        // display limited header value here for public eyes
        // TODO: escape line breaks to prevent header hack
        $author = $this->header['author'] ?? '';
        $authorHeaderName = Post::headerHumanName('author');
        $time = $this->header['time'] ?? '';
        $timeHeaderName = Post::headerHumanName('time');
        return "{$authorHeaderName}：{$author}\n{$timeHeaderName}：{$time}\n";
    }

    public function htmlHeader(): string {
        return nl2br($this->safeHeader(), false);
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
        $getLines = function () {
            $lines = explode("\n", trim($this->body));
            foreach ($lines as $line) {
                yield $line . "\n";
            }
        };
        $lines = Filter::autoParagraph(Filter::quoteToBlockquote($getLines()));
        return nl2br(implode('', iterator_to_array($lines)), false);
    }
}
