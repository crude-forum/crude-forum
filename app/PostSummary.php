<?php

/**
 * Class for post summary objects.
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Post.php
 * Source Code
 */

namespace CrudeForum\CrudeForum;

use \CrudeForum\CrudeForum\Exception\PostInvalid;

/**
 * Class for post summary objects.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Post.php
 * Source Code
 */
class PostSummary
{

    public $id = '';
    public $level = 0;
    public $title = '';
    public $author = '';
    public $time = '';

    /**
     * Constructor of a PostSummary
     *
     * @param string  $id     ID of a Post.
     * @param integer $level  Level of reply under a thread.
     * @param string  $title  Title of the Post.
     * @param string  $author Author name of the Post.
     * @param string  $time   Time when the Post is created.
     * @param integer $pos    Position of the Post within the forum index.
     */
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

    /**
     * Create PostSummary from a given Post object.
     * Note that the level is always 0.
     *
     * @param Post $post Post to create PostSummary from.
     *
     * @return PostSummary|null
     */
    public static function fromPost(Post $post): ?PostSummary
    {
        if ($post->id === null) {
            throw PostInvalid('post has no id');
            return null;
        }
        if (empty($post->title)) {
            throw PostInvalid('post has no title');
            return null;
        }
        if (!isset($post->header['author'])) {
            throw PostInvalid('post has undefined author');
            return null;
        }
        if (!isset($post->header['time'])) {
            throw PostInvalid('post has undefined time');
            return null;
        }
        return new PostSummary($post->id, 0, $post->title, $post->header['author'], $post->header['time']);
    }

    /**
     * Create a PostSummary object from its plain text line storage format.
     *
     * @param string  $line The plain text line storage format.
     * @param integer $pos  The position of the Post in the index.
     *
     * @return PostSummary|null The PostSummary object, or if error, null.
     */
    public static function fromIndexLine(string $line, $pos=0): ?PostSummary
    {
        list($id, $level, $title, $author, $time) = explode("\t", $line);
        return new PostSummary($id, $level, $title, $author, $time, $pos);
    }

    /**
     * Returns the plain text line storage format of the PostSummary.
     *
     * @return string The plain text line storage format.
     */
    public function toIndexLine(): string
    {
        return sprintf(
            "%d\t%d\t%s\t%s\t%s\n",
            $this->id,
            $this->level,
            $this->title,
            $this->author,
            $this->time
        );
    }
}
