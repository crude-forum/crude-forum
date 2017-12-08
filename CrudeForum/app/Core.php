<?php

namespace ywsing\CrudeForum;

class Core {

    public $dataDirectory;
    public $logDirectory;

    public function __construct($config) {
        $this->dataDirectory = $config['dataDirectory'] ?? '';
        $this->logDirectory  = $config['logDirectory'] ?? '';
        $this->administrator = $config['administrator'] ?? '';
    }

    public function readPost(string $postID): Post {
        $subdir = floor((int) $postID / 1000) . "/";
        $fh = fopen($this->dataDirectory . $subdir . $postID, "r+");
        return Post::fromFile($fh);
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
           (empty($administrator) || ($user != $administrator)) &&

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
