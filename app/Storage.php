<?php

/**
 * Abstraction of storage engine of the CrudeForum
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */

namespace CrudeForum\CrudeForum;

use \CrudeForum\CrudeForum\ForumIndex;
use \CrudeForum\CrudeForum\Post;
use \CrudeForum\CrudeForum\PostSummary;
use \CrudeForum\CrudeForum\Lock;
use \Generator;

/**
 * Abstraction of storage engine of the CrudeForum
 *
 * @category Interface
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Storage/FileStorage.php
 * Source Code
 */
interface Storage
{
    /**
     * Gets the ForumIndex from storage.
     *
     * @return ForumIndex|null
     */
    public function getIndex(): ?ForumIndex;

    /**
     * Get a generator of post that would generate
     * Post from newest to oldest.
     *
     * @return callable
     */
    public function getPosts(): Generator;

    /**
     * Get the current post count from storage.
     *
     * @return integer number of post, as recorded by the storage.
     */
    public function getCount(): int;

    /**
     * Increment the post count in storage.
     *
     * @return int The new count after increment.
     */
    public function incCount(): int;

    /**
     * Read a certain post of the given postID
     *
     * @param string $postID ID of the post
     *
     * @return Post|null The post, or null if not found
     */
    public function readPost(string $postID): ?Post;

    /**
     * Write a post into storage, of the given postID.
     *
     * @param integer $postID The ID of the post.
     * @param Post    $post   The post object to store.
     *
     * @return boolean
     */
    public function writePost(int $postID, Post $post): bool;

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
    ): bool;

    /**
     * Get lock gets a file lock, which locks the forum read/write operations
     *
     * @return resource
     */
    public function getLock(): Lock;

    /**
     * Write a log, of given context, into storage.
     *
     * @param array  $context The context for logging.
     * @param string $msg     The log message string.
     *
     * @return void
     */
    public function writeLog(array $context, string $msg);
}