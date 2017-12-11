<?php

namespace ywsing\CrudeForum;

use \ywsing\CrudeForum\Iterator\FileObject;

class Core {

    public $dataDirectory;
    public $logDirectory;

    public function __construct($config) {
        $this->dataDirectory = $config['dataDirectory'] ?? '';
        $this->logDirectory  = $config['logDirectory'] ?? '';
        $this->administrator = $config['administrator'] ?? '';
    }

    public function getIndex(): ?ForumIndex {
        $indexfn = $this->dataDirectory . "index";
        if(!file_exists ($indexfn) && !touch ($indexfn)) {
            throw new Exception("unable to create index file: {$indexfn}");
            return NULL;
        }
        return new ForumIndex(new FileObject(fopen($indexfn, 'r+')));
    }

    public function getCount(): int {
        // Gets messages count for assigning post number
        $countfn = $this->dataDirectory . "count";
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
        $countfn = $this->dataDirectory . "count";
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

    public function readPrevPostSummary(string $postID): ?PostSummary {
        $prevSummary = NULL;
        try {
            $index = $this->getIndex();
            foreach ($index as $postSummary) {
                if ($postSummary->id == $postID) {
                    if ($prevSummary !== NULL) return $prevSummary;
                    throw new \Exception("post #{$postID} has no previous post");
                }
                $prevSummary = $postSummary;
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return NULL;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return NULL;
    }

    public function readNextPostSummary(string $postID): ?PostSummary {
        $prevSummary = NULL;
        try {
            $index = $this->getIndex();
            foreach ($index as $postSummary) {
                if (($prevSummary !== NULL) && ($prevSummary->id == $postID)) {
                    return $postSummary;
                }
                $prevSummary = $postSummary;
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return NULL;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return NULL;
    }

    public function readPostSummary(string $postID): ?PostSummary {
        try {
            $index = $this->getIndex();
            foreach ($index as $postSummary)
                if ($postSummary->id == $postID) return $postSummary;
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('failed to open forum index');
            return NULL;
        }
        throw new \Exception("there is no post #{$postID} in forum index");
        return NULL;
    }

    public function readPost(string $postID): ?Post {

        // determine post file full path
        $subdir = ($postID === 'notes') ?
            '' : floor((int) $postID / 1000) . "/";
        $postFn = $this->dataDirectory . $subdir . $postID;

        // attempt to create if accessing note file
        if(($postID === 'notes') && !file_exists($postFn) && !touch($postFn)) {
            throw new Exception('unable to create notes file');
            return NULL;
        }

        // open post file
        $fh = fopen($postFn, "r+");
        if (!is_resource($fh)) {
            throw new \Exception("failed to open post #{$postID}");
            return NULL;
        }

        // read post from file
        $title = trim(fgets($fh, 4096));
        fgets($fh, 4096); // get rid of an empty line
        $body  = '';
        $noAutoBr = FALSE;
        while(!feof($fh)) {
            $line = fgets($fh, 4096);
            if (is_string(strstr($line, 'NO-LINE-END-BR'))) $noAutoBr = TRUE;
            else $body .= $line;
        }
        return new Post($title, $body, $noAutoBr);
    }

    public function writePost(int $postID, Post $post) {

        // determine data folder for the post
        $subdir = floor((int) $postID / 1000) . "/";
        if (!is_dir($this->dataDirectory . $subdir)) {
            mkdir($this->dataDirectory . $subdir);
            chmod($this->dataDirectory . $subdir, 0777);
        }

        // determine post file fullpath
        $postFn = $this->dataDirectory . $subdir . $postID;
        $fh = fopen($postFn, "w+");
        fputs($fh, sprintf("%s\n\n%s", $post->title, $post->body));
        fclose($fh);
    }

    public function appendIndex(PostSummary $postSummary, $parentID=FALSE) {
        rename($this->dataDirectory . "index", $this->dataDirectory . "index.old");

        $fh_old = fopen($this->dataDirectory . "index.old", "r+");
        if (!is_resource($fh_old)) {
            throw new \Exception('unable to open index.old for read');
            return FALSE;
        }
        $oldIndex = new ForumIndex(new FileObject($fh_old));

        $fh = fopen($this->dataDirectory . "index", "w+");
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
            unlink($this->dataDirectory . 'index.old');
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
        unlink($this->dataDirectory . 'index.old');
        return TRUE;
    }

    public function getLock() {

        $lockfn = $this->dataDirectory . "lock";
        if(!file_exists ($lockfn) && !touch ($lockfn)) {
            throw new Exception("unable to create lock file: {$lockfn}");
        }
        $lock = fopen ($lockfn, "r+");
        if(!$lock || !flock ($lock, LOCK_EX)) {
            throw new Exception("Unable to get lock");
        }

        return $lock;
    }

    public function log ($msg) {

        $logfn = $this->logDirectory . '/log';

        if(!file_exists ($logfn) && !touch ($logfn)) {
            throw new Exception("unable to create log file: {$logfn}");
        }
        if(file_exists ($logfn) &&
            filesize ($logfn) >= 32768)
            rename ($logfn, $logfn . '.' . strftime ("%Y%m%d"));

        if(!isset ($_COOKIE["forumName"])) {
            if(!isset ($_COOKIE["forumCDROM"])) {
                $user = rand (0, 16384);
                setcookie ("forumCDROM", $user, mktime (0, 0, 0, 1, 1, 2038), "/");
            } else $user = $_COOKIE["forumCDROM"];
        }
        else $user = $_COOKIE["forumName"];

        $remoteAddr = getenv ("REMOTE_ADDR");
        $userAgent = getenv ("HTTP_USER_AGENT");

        if(
           /* Do not log me */
           (empty($this->administrator) || ($user != $this->administrator)) &&

           /* Do not log bots */
           !is_string (strstr ($userAgent, "http://search.msn.com/msnbot.htm")) &&
           !is_string (strstr ($userAgent, "Free Eating Union")) &&
           !is_string (strstr ($userAgent, "ia_archiver")) &&
           !is_string (strstr ($userAgent, "sogou spider")) &&
           !is_string (strstr ($userAgent, "Baiduspider+(+")) &&
           !is_string (strstr ($userAgent, "Yahoo! Slurp")) &&
           !is_string (strstr ($userAgent, "Googlebot"))
        ) {
            $log = fopen ($logfn, "a");
            if($log)
                fputs ($log, sprintf ("%s %s %s %s %s %s\n",
                    strftime ("%a %b %d %X %Y"),
                    $remoteAddr,
                    getenv ("HTTP_X_FORWARDED_FOR"),
                    $user,
                    $userAgent,
                    $msg)
                );
            fclose ($log);
        }
    }

}
