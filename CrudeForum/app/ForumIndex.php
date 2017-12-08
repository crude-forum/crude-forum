<?php

namespace ywsing\CrudeForum;

class ForumIndex implements \Iterator {

    private $position = 0;
    private $fh = FALSE;
    private $currentLine = FALSE;

    public function __construct($fh) {
        if (!is_resource($fh))
            throw new \InvalidArgumentException('not a valid resource');
        $this->fh = $fh;
        $this->read();
    }

    public function  __destruct() {
        if ($this->fh !== FALSE) fclose($this->fh);
        $this->fh = FALSE;
    }

    private function read() {
        if (($this->currentLine = fgets($this->fh, 4096)) === FALSE)
            return $this->currentLine;
        $this->currentLine = trim($this->currentLine);
        return $this->currentLine;
    }

    public function current() {
        if ($this->fh === FALSE)
            throw new \Exception('forum index has already closed');
        return PostSummary::fromIndexLine(
            $this->currentLine, $this->position);
    }

    public function key() {
        if ($this->fh === FALSE)
            throw new \Exception('forum index has already closed');
        return $this->position;
    }

    public function next() {
        if ($this->fh === FALSE)
            throw new \Exception('forum index has already closed');
        $this->position++;
    }

    public function rewind() {
        if ($this->fh === FALSE)
            throw new \Exception('forum index has already closed');
        rewind($this->fh);
    }

    public function valid() {
        if ($this->fh === FALSE)
            throw new \Exception('forum index has already closed');
        try {
            $this->read();

            // skip empty lines
            while (
                ($this->currentLine !== FALSE) &&
                empty($this->currentLine)
            ) {
                $this->read();
            }
            return ($this->currentLine !== FALSE);
        } catch (\Exception $e) {
            return FALSE;
        }
    }
}
