<?php

/**
 * Bootstrapping the main objects to use in the forum
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

/**
 * Core provides access for bootstraping the forum.
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
     * Turn e-mail like prefix-based quote into html blockquote tag.
     *
     * @param string $string
     * @param string $quote
     * @return void
     */
    public static function quoteToBlockquote(string $string, string $quote='|') {
        $output = '';
        $lines = explode("\n", $string);
        $regex = '/^([' . preg_quote($quote) . ' ]+)(.*)$/';
        $prevLevel = 0;

        foreach ($lines as $line) {

            // determine level
            // separate quote and contents
            if (preg_match($regex, $line, $matches)) {
                $level = substr_count($matches[1], '|');
            } else {
                $level = 0;
                $matches = [$line, '', $line];
            }

            // adding blockquote open and end tag for stepping up / down
            if (($diff = $level - $prevLevel) > 0) {
                $output .= str_repeat('<blockquote>', $diff);
            } else if ($diff < 0) {
                $output .= str_repeat('</blockquote>', -$diff);
            }

            // append contents
            $output .= $matches[2] . "\n";
            $prevLevel = $level;
        }

        return $output;
    }
}