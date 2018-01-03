<?php

namespace CrudeForum\CrudeForum;

use \CrudeForum\CrudeForum\Iterator\FileObject;

class Storage {

    private $dataDirectory;
    private $logDirectory;
    private $lock;

    public function __construct($config) {
        $this->dataDirectory = rtrim($config['dataDirectory'] ?? '', '/');
        $this->logDirectory = rtrim($config['logDirectory'] ?? '', '/');
    }

    public function getIndex(): ?ForumIndex {
        $indexfn = $this->dataDirectory . '/index';
        if(!file_exists ($indexfn) && !touch ($indexfn)) {
            throw new Exception("unable to create index file: {$indexfn}");
            return NULL;
        }
        return new ForumIndex(new FileObject(fopen($indexfn, 'r+')));
    }

    public function getCount(): int {
        // Gets messages count for assigning post number
        $countfn = $this->dataDirectory . '/count';
        if(!file_exists ($countfn) && !touch ($countfn)) {
            throw new Exception("unable to create count file: {$countfn}");
        }

        $countFile = fopen($countfn, "r");
        if (!is_resource($countFile)) {
            throw new \Exception('cannot open count file for reading');
            return 0;
        }
        fscanf($countFile, "%d", $count);
        fclose($countFile);
        return ($count === NULL) ? 0 : $count;
    }

    public function incCount() {
        // Gets messages count for assigning post number
        $countfn = $this->dataDirectory . '/count';
        if(!file_exists ($countfn) && !touch ($countfn)) {
            throw new Exception("unable to create count file: {$countfn}");
        }

        $countFile = fopen($countfn, "r+");
        if (!is_resource($countFile)) {
            throw new \Exception('cannot open count file for reading and writing');
        }
        fscanf($countFile, "%d", $count);
        rewind($countFile);
        $count = ($count === NULL) ? 0 : $count;
        fputs($countFile, ++$count);
        fclose($countFile);
    }

    public function readPost(string $postID): ?Post {

        // determine post file full path
        $subdir = ($postID === 'notes') ?
            '/' : '/' . floor((int) $postID / 1000) . '/';
        $postFn = $this->dataDirectory . $subdir . $postID;

        // attempt to create if accessing note file
        if(($postID === 'notes') && !file_exists($postFn) && !touch($postFn)) {
            throw new Exception('unable to create notes file');
            return NULL;
        }

        // read post with text
        return Post::fromText(file_get_contents($postFn));
    }

    public function writePost(int $postID, Post $post): bool {
        // determine data folder for the post
        $subdir = floor((int) $postID / 1000);
        if (!is_dir($this->dataDirectory . '/' . $subdir)) {
            if (mkdir($this->dataDirectory . '/' . $subdir) === FALSE) {
                throw new \Exception('failed to create subdirectory for the post');
                return FALSE;
            }
            if (chmod($this->dataDirectory . '/' . $subdir, 0777) === FALSE) {
                throw new \Exception('failed to chmod post subdirectory');
                return FALSE;
            }
        }
        $fh = fopen($this->dataDirectory . '/' . $subdir  . '/' . $postID, "w+");
        if (!is_resource($fh)) {
            throw new \Exception('failed to open post file to write');
            return FALSE;
        }
        if (!fputs($fh, sprintf("%s\n\n%s\n\n%s",
            $post->title,
            implode("\n", $post->storageHeader()),
            $post->body
        ))) {
            throw new \Exception('failed to write to post file');
            return FALSE;
        }
        if (!fclose($fh)) {
            throw new \Exception('failed to close post file');
            return FALSE;
        }
        return TRUE;
    }

    public function appendIndex(PostSummary $postSummary, $parentID=FALSE): bool {

        // swap the index out and prepare to rewrite index
        if (!rename($this->dataDirectory . '/index', $this->dataDirectory . '/index.old')) {
            throw new \Exception('unable to rename index as index.old');
            return FALSE;
        }

        try {
            $fh_old = fopen($this->dataDirectory . '/index.old', 'r+');
            if (!is_resource($fh_old)) {
                throw new \Exception('unable to open index.old for read');
                return FALSE;
            }
            $oldIndex = new ForumIndex(new FileObject($fh_old));

            $fh = fopen($this->dataDirectory . '/index', 'w+');
            if (!is_resource($fh)) {
                throw new \Exception('unable to open index for write');
                return FALSE;
            }

            // if this is not a reply
            if ($parentID === FALSE) {
                fputs($fh, $postSummary->toIndexLine());
                foreach ($oldIndex as $oldPostSummary) {
                    fputs($fh, $oldPostSummary->toIndexLine());
                }
                unset($index);
                unlink($this->dataDirectory . '/index.old');
                return TRUE;
            }

            // if this is a reply
            $parentID = (int) $parentID;
            foreach ($oldIndex as $pos => $oldPostSummary) {
                fputs($fh, $oldPostSummary->toIndexLine());
                // append to below parent
                if ($oldPostSummary->id == $parentID) {
                    $postSummary->level = $oldPostSummary->level + 1;
                    $postSummary->pos = $pos + 1;
                    fputs($fh, $postSummary->toIndexLine());
                }
            }
            fclose($fh);

            // close and remove old index
            unset($oldIndex);
            unlink($this->dataDirectory . '/index.old');
            return TRUE;

        } catch (\Exception $e) {
            // restore index
            if (!rename($this->dataDirectory . '/index.old', $this->dataDirectory . '/index')) {
                throw new \Exception('unable to restore index.old as index');
                return FALSE;
            }
            throw $e;
            return FALSE;
        }
        return FALSE;
    }

    public function getLock() {

        $lockfn = $this->dataDirectory . '/lock';
        if(!file_exists ($lockfn) && !touch ($lockfn)) {
            throw new Exception("unable to create lock file: {$lockfn}");
        }
        $lock = fopen($lockfn, "r+");
        if(!$lock || !flock ($lock, LOCK_EX)) {
            throw new Exception("Unable to get lock");
        }

        return $lock;
    }

    public function writeLog($context, $msg='') {

        $logfn = $this->logDirectory . '/log';

        if (!file_exists ($logfn) && !touch ($logfn)) {
            throw new Exception("unable to create log file: {$logfn}");
        }
        if (file_exists ($logfn) && filesize($logfn) >= 32768)
            rename($logfn, $logfn . '.' . strftime ("%Y%m%d"));

        $logFile = fopen($logfn, "a");
        if ($logFile)
            fputs($logFile, sprintf("%s %s %s %s %s %s\n",
                $context['time'],
                $context['remoteAddr'],
                $context['xForwardedFor'],
                $context['user'],
                $context['userAgent'],
                $msg
            ));
        fclose ($logFile);
    }
}