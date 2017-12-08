<?php

namespace ywsing\CrudeForum;

class PostSummary {

    public $id = '';
    public $level = 0;
    public $title = '';
    public $author = '';
    public $time = '';

    public function __construct(
        $id='',
        $level=0,
        $title='',
        $author='',
        $time='',
        $pos=0
    ) {
        $this->id = $id;
        $this->level = $level;
        $this->title = $title;
        $this->author = $author;
        $this->time = $time;
        $this->pos = $pos;
    }

    public static function fromIndexLine(string $line, $pos=0): ?PostSummary {
        list($id, $level, $title, $author, $time) = explode("\t", $line);
        return new PostSummary($id, $level, $title, $author, $time, $pos);
    }

    public function toIndexLine(): string {
        return sprintf("%d\t%d\t%s\t%s\t%s",
            $this->id,
            $this->level,
            $this->title,
            $this->author,
            $this->time
        );
    }
}
