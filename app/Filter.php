<?php

/**
 * General text filtering functions collection that filter stream of text lines
 * from Generator, and return Generator of filtered stream.
 *
 * PHP Version 7.1
 *
 * @file     Filter.php
 * @category File
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Filter.php
 * Source Code
 */

namespace CrudeForum\CrudeForum;

use \Generator;

/**
 * General text filtering functions collection that filter stream of text lines
 * from Generator, and return Generator of filtered stream.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/Filter.php
 * Source Code
 */

class Filter
{

    /**
     * Turn URL into html href tags.
     *
     * @param Generator $lines Generator of text lines of a string.
     *
     * @return Generator Generator of text lines of a string.
     */
    public static function autoLink(Generator $lines): Generator
    {
        return (function () use ($lines) {
            $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
            foreach ($lines as $line) {
                yield preg_replace(
                    $url, '<a href="$0" target="_blank" title="$0">$0</a>', $line
                );
            }
        })();
    }

    /**
     * Turn double line breaked text blocks into html paragraphs
     *
     * @param Generator $lines Generator of text lines of a string.
     *
     * @return Generator Generator of text lines of a string.
     */
    public static function autoParagraph(Generator $lines): Generator
    {
        return (function () use ($lines) {
            $buffer = '';
            foreach ($lines as $line) {

                $suffix = '';

                // remove blockquote start block
                while (preg_match('/^<blockquote>(.*)(\n*)$/', $line, $matches)) {
                    yield '<blockquote>';
                    $line = $matches[1] . $matches[2];
                }

                // put close tag in separated buffer
                while (preg_match('/^(.*)<\/blockquote>(\n*)$/', $line, $matches)) {
                    $suffix .= '</blockquote>';
                    $line = $matches[1] . $matches[2];
                }

                if (trim($line) === '') {
                    // see if line is empty, if so, flush buffer as paragraph
                    yield '<p>' . trim($buffer) . '</p>' . $suffix;
                    $buffer = '';
                } else {
                    // append line to buffer
                    $buffer .= $line;
                }
            }

            // flush the remaining buffer, if not empty
            if (trim($buffer) !== '') {
                yield '<p>' . trim($buffer) . '</p>';
            }
        })();
    }

    /**
     * Turn e-mail like prefix-based quote into html blockquote tag.
     *
     * @param Generator $lines Generator of text lines of a string.
     * @param string    $quote Character for quoting text block (e.g. '>', '|').
     *
     * @return Generator Generator of text lines of a string.
     */
    public static function quoteToBlockquote(
        Generator $lines, string $quote='|'
    ): Generator {
        return (function () use ($lines, $quote) {
            $regex = '/^([' . preg_quote($quote) . ' ]+)(.*)(\n*)$/A';
            $prevLevel = 0;
            foreach ($lines as $line) {

                // determine level
                // separate quote and contents
                if (preg_match($regex, $line, $matches)) {
                    $level = substr_count($matches[1], '|');
                } else {
                    $level = 0;
                    $matches = [$line, '', trim($line), "\n"];
                }

                // adding blockquote open and end tag for stepping up / down
                $prefix = '';
                if (($diff = $level - $prevLevel) > 0) {
                    $prefix = str_repeat('<blockquote>', $diff);
                } else if ($diff < 0) {
                    $prefix = str_repeat('</blockquote>', -$diff);
                }

                // remember level
                $prevLevel = $level;

                // yield contents
                yield $prefix . $matches[2] . $matches[3];
            }
        })();
    }
}