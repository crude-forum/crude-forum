<?php

/**
 * Storage engine for storing index, post, post count and locking mechanism.
 *
 * PHP Version 7.1
 *
 * @file     FileStorage.php
 * @category File
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */

namespace CrudeForum\CrudeForum\Storage;

use \CrudeForum\CrudeForum\Iterator\FileObject;
use \CrudeForum\CrudeForum\ForumIndex;
use \CrudeForum\CrudeForum\Post;
use \CrudeForum\CrudeForum\PostSummary;
use \CrudeForum\CrudeForum\Storage;
use \CrudeForum\CrudeForum\Lock;
use \CrudeForum\CrudeForum\Exception\PostNotFound;
use \CrudeForum\CrudeForum\Exception\PostInvalid;
use \Exception;
use \Generator;

/**
 * Storage engine for storing index, post, post count and locking mechanism.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */
class FileStorage implements Storage
{

    private $_dataDirectory;
    private $_logDirectory;
    private $_lock;

    /**
     * Construct method
     *
     * @param array $config array of configuration
     */
    public function __construct(array $config)
    {
        $this->_dataDirectory = rtrim($config['dataDirectory'] ?? '', '/');
        $this->_logDirectory = rtrim($config['logDirectory'] ?? '', '/');

        // validate dataDirectory
        FileStorage::_ensureDir(
            'dataDirectory', 'CRUDE_DIR_DATA', $config['dataDirectory']
        );
        FileStorage::_ensureDir(
            'logDirectory', 'CRUDE_DIR_LOG', $config['logDirectory']
        );
    }

    /**
     * Make sure that the configured directory exists in the file system
     * and is writable
     *
     * @param string $configName The configuration variable name.
     * @param string $envName    The expected environment variable name.
     * @param string $dirname    The directory name.
     *
     * @return boolean Return true if the directory exists and writable.
     *                 Return false otherwise.
     */
    private static function _ensureDir($configName, $envName, $dirname): bool
    {
        if (empty($dirname)) {
            throw new Exception(sprintf('%s is empty.', $envName));
            return false;
        }
        if (!file_exists($dirname) && !@mkdir($dirname, 0777, true)) {
            throw new Exception(sprintf('unable to create directory: %s', $dirname));
            return false;
        }
        if (!is_dir($dirname)) {
            throw new Exception(sprintf('%s is not a directory.', $dirname));
            return false;
        }
        if (!is_writable($dirname)) {
            throw new Exception(sprintf('%s is not writable.', $dirname));
            return false;
        }
        return true;
    }

    /**
     * Gets the ForumIndex from storage.
     *
     * @return ForumIndex|null
     */
    public function getIndex(): ?ForumIndex
    {
        $indexfn = $this->_dataDirectory . '/index';
        if (!file_exists($indexfn) && !touch($indexfn)) {
            throw new Exception("unable to create index file: {$indexfn}");
            return null;
        }
        return new ForumIndex(new FileObject(fopen($indexfn, 'r+')));
    }

    /**
     * Get a generator of post that would generate
     * Post from newest to oldest.
     *
     * @return callable
     */
    public function getPosts(): Generator
    {
        return (function (FileStorage $forum) {
            for ($i = $forum->getCount(); $i>0; $i--) {
                try {
                    if (($post = $forum->readPost($i)) != null) {
                        yield $post;
                    }
                } catch (Exception $e) {
                    // ignore normal post errors.
                    switch (true) {
                    case ($e instanceof PostNotFound):
                        continue 2;
                    case ($e instanceof PostInvalid):
                        continue 2;
                    default:
                        throw $e;
                    }
                }
            }
        })($this);
    }

    /**
     * Get the current post count from storage.
     *
     * @return integer number of post, as recorded by the storage.
     */
    public function getCount(): int
    {
        // Gets messages count for assigning post number
        $countfn = $this->_dataDirectory . '/count';
        if (!file_exists($countfn) && !touch($countfn)) {
            throw new Exception("unable to create count file: {$countfn}");
        }

        $countFile = fopen($countfn, "r");
        if (!is_resource($countFile)) {
            throw new Exception('cannot open count file for reading');
            return 0;
        }
        fscanf($countFile, "%d", $count);
        fclose($countFile);
        return ($count === null) ? 0 : $count;
    }

    /**
     * Increment the post count in storage.
     *
     * @return int The new count after increment.
     */
    public function incCount(): int
    {
        // Gets messages count for assigning post number
        $countfn = $this->_dataDirectory . '/count';
        if (!file_exists($countfn) && !touch($countfn)) {
            throw new Exception("unable to create count file: {$countfn}");
        }

        $countFile = fopen($countfn, "r+");
        if (!is_resource($countFile)) {
            throw new Exception('cannot open count file for reading and writing');
        }
        fscanf($countFile, "%d", $count);
        rewind($countFile);
        $count = ($count === null) ? 0 : $count;
        fputs($countFile, ++$count);
        fclose($countFile);

        return $count;
    }

    /**
     * Read a certain post of the given postID
     *
     * @param string $postID ID of the post
     *
     * @return Post|null The post, or null if not found
     */
    public function readPost(string $postID): ?Post
    {

        // determine post file full path
        $subdir = ($postID === 'notes') ?
            '/' : '/' . floor((int) $postID / 1000) . '/';
        $postFn = $this->_dataDirectory . $subdir . $postID;

        // attempt to create if accessing note file
        if (($postID === 'notes') && !file_exists($postFn) && !touch($postFn)) {
            throw new Exception('unable to create notes file');
            return null;
        }

        // read post with text
        if (!file_exists($postFn)) {
            throw new PostNotFound($postID);
        }
        $post = Post::fromText(file_get_contents($postFn));
        $post->id = $postID;
        return $post;
    }

    /**
     * Write a post into storage, of the given postID.
     *
     * @param integer $postID The ID of the post.
     * @param Post    $post   The post object to store.
     *
     * @return boolean
     */
    public function writePost(int $postID, Post $post): bool
    {
        // determine data folder for the post
        $subdir = floor((int) $postID / 1000);
        if (!is_dir($this->_dataDirectory . '/' . $subdir)) {
            if (mkdir($this->_dataDirectory . '/' . $subdir) === false) {
                throw new Exception('failed to create subdirectory for the post');
                return false;
            }
            if (chmod($this->_dataDirectory . '/' . $subdir, 0777) === false) {
                throw new Exception('failed to chmod post subdirectory');
                return false;
            }
        }
        $fh = fopen($this->_dataDirectory . '/' . $subdir  . '/' . $postID, "w+");
        if (!is_resource($fh)) {
            throw new Exception('failed to open post file to write');
            return false;
        }

        $logLine = sprintf(
            "%s\n\n%s\n\n%s",
            $post->title,
            implode("\n", $post->storageHeader()),
            $post->body
        );
        if (!fputs($fh, $logLine)) {
            throw new Exception('failed to write to post file');
            return false;
        }
        if (!fclose($fh)) {
            throw new Exception('failed to close post file');
            return false;
        }
        return true;
    }

    /**
     * Append a PostSummary to the index in the storage.
     *
     * @param PostSummary $postSummary The PostSummary to store.
     * @param string|null $parentID    The ID of the parent post.
     *
     * @return boolean
     */
    public function appendIndex(
        PostSummary $postSummary,
        ?string $parentID=null
    ): bool {

        // swap the index out and prepare to rewrite index
        $indexFn = $this->_dataDirectory . '/index';
        $oldIndexFn = $this->_dataDirectory . '/index.old';
        if (!rename($indexFn, $oldIndexFn)) {
            throw new Exception('unable to rename index as index.old');
            return false;
        }

        try {
            $fh_old = fopen($oldIndexFn, 'r+');
            if (!is_resource($fh_old)) {
                throw new Exception('unable to open index.old for read');
                return false;
            }
            $oldIndex = new ForumIndex(new FileObject($fh_old));

            $fh = fopen($indexFn, 'w+');
            if (!is_resource($fh)) {
                throw new Exception('unable to open index for write');
                return false;
            }

            // if this is not a reply
            if ($parentID === null) {
                fputs($fh, $postSummary->toIndexLine());
                foreach ($oldIndex as $oldPostSummary) {
                    fputs($fh, $oldPostSummary->toIndexLine());
                }
                unset($index);
                unlink($oldIndexFn);
                return true;
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
            unlink($oldIndexFn);
            return true;

        } catch (Exception $e) {
            // restore index
            if (!rename($oldIndexFn, $indexFn)) {
                throw new Exception('unable to restore index.old as index');
                return false;
            }
            throw $e;
            return false;
        }
        return false;
    }

    /**
     * Get lock gets a file lock, which locks the forum read/write operations
     *
     * @return resource
     */
    public function getLock(): Lock
    {
        $this->_lock = new FileStorageLock($this->_dataDirectory . '/lock');
        return $this->_lock;
    }

    /**
     * Write a log, of given context, into storage.
     *
     * @param array  $context The context for logging.
     * @param string $msg     The log message string.
     *
     * @return void
     */
    public function writeLog(array $context, string $msg='')
    {

        $logfn = $this->_logDirectory . '/log';

        if (!file_exists($logfn) && !touch($logfn)) {
            throw new Exception("unable to create log file: {$logfn}");
        }
        if (file_exists($logfn) && filesize($logfn) >= 32768) {
            rename($logfn, $logfn . '.' . date('Ymd'));
        }

        $logFile = fopen($logfn, "a");
        if ($logFile) {
            fputs(
                $logFile,
                sprintf(
                    "%s %s %s %s %s %s\n",
                    $context['time'],
                    $context['remoteAddr'],
                    $context['xForwardedFor'],
                    $context['user'],
                    $context['userAgent'],
                    $msg
                )
            );
        }
        fclose($logFile);
    }
}
