<?php

/**
 * A file based lock with mandatory lock mechanism of flock.
 *
 * PHP Version 8.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */

namespace CrudeForum\CrudeForum\Storage;

use \CrudeForum\CrudeForum\Lock;

/**
 * A file based lock with mandatory lock mechanism of flock.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum\Storage
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */
class FileStorageLock implements Lock
{

    private $_fh = false;

    /**
     * Constructor of the file based lock. Will attempt to create the file
     * if not exists. Failing to create, open or lock the file will results
     * in Exception.
     *
     * @param string $filename Filename of the lock file.
     */
    public function __construct(string $filename)
    {
        if (!file_exists($filename) && !touch($filename)) {
            throw new \Exception("unable to create lock file: {$filename}");
        }
        $this->_fh = fopen($filename, "r+");
        if (!$this->_fh || !flock($this->_fh, LOCK_EX)) {
            throw new \Exception("Unable to get lock");
        }
    }

    /**
     * Destructor of the lock object. Will automatically unlock it.
     */
    public function __destruct()
    {
        $this->unlock();
    }

    /**
     * Unlock the lock
     *
     * @inheritDoc
     *
     * @return bool
     */
    public function unlock(): bool {
        if ($this->_fh !== false) {
            fclose($this->_fh);
            $this->_fh = false;
        }
        return true;
    }
}