<?php

/**
 * General text filtering functions collection that filter stream of text lines
 * from Generator, and return Generator of filtered stream.
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum Source Code
 */

namespace CrudeForum\CrudeForum;

use \Phata\Widgetfy\Core as Widgetfy;
use \Phata\Widgetfy\Theme as WidgetfyTheme;
use \Generator;

/**
 * General text filtering functions collection that filter stream of text lines
 * from Generator, and return Generator of filtered stream.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum Source Code
 */

class StreamFilter
{

    /**
     * Turn string into a generator of its lines.
     *
     * @param string $string    A string to be exploded.
     * @param string $delimiter The delimiter string for exploded. (default "\n")
     *
     * @return Generator
     */
    public static function stringToPipe(string $string, string $delimiter="\n"): Generator
    {
        return (function () use ($string, $delimiter) {
            $lines = explode($delimiter, trim($string));
            foreach ($lines as $line) {
                yield $line . "\n";
            }
        })();
    }

    /**
     * Implode a generator of string with a given glue (default '').
     *
     * @param Generator $stream A Generator of strings.
     * @param string    $glue   The glue for implode. (default: '')
     *
     * @return string The imploded string from the stream.
     */
    public static function pipeToString(Generator $stream, string $glue=''): string
    {
        return implode($glue, iterator_to_array($stream));
    }

    /**
     * Piping multiple filter
     *
     * @param callable ...$filters A list of filters to apply to a Generator.
     *
     * @return callable A single filter function that converts a Generator
     *                  into another.
     */
    public static function pipe(callable ...$filters): callable
    {
        return function (Generator $lines) use ($filters): Generator {
            $size = sizeof($filters);
            $output = $lines;
            for ($i = 0; $i < $size; $i++) {
                $output = call_user_func($filters[$i], $output);
            }
            return $output;
        };
    }

    /**
     * Create a pipe, from given filters, to filter string function.
     *
     * @param callable ...$filters A list of filters to apply to a string.
     *
     * @return callable A single filter function that filter a string.
     */
    public static function pipeString(callable ...$filters): callable
    {
        return function (string $string) use ($filters): string {
            $filter = \call_user_func_array('\CrudeForum\CrudeForum\StreamFilter::pipe', $filters);
            return StreamFilter::pipeToString(
                $filter(
                    StreamFilter::stringToPipe($string)
                )
            );
        };
    }

    /**
     * Turn URL into html href tags.
     *
     * @param Generator $lines Generator of text lines of a string.
     *
     * @return Generator Generator of text lines of a string.
     */
    public static function autoLink(Generator $lines): Generator
    {
        $regex = '~((?<![="\'])(https?)://([^\s<]+)|(?<!\/)(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
        return (function () use ($lines, $regex) {
            foreach ($lines as $line) {
                yield preg_replace(
                    $regex, '<a href="$0" target="_blank" title="$0">$0</a>', $line
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
     * Turn lines with a single URL, if possible, a video embed widget.
     *
     * @param Generator $lines   Generator of text lines of a string.
     * @param array     $options Options for Widgetfy::translate function
     *
     * @return Generator
     */
    public static function autoWidgetfy(Generator $lines, array $options=[]): Generator {
        $regex = '~^((?<![="\'])(https?)://([^\s<]+)|(?<!\/)(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])$~i';
        return (function () use ($lines, $regex, $options) {
            foreach ($lines as $line) {
                if (preg_match($regex, trim($line), $matches)) {
                    if (($embed = Widgetfy::translate($matches[1])) !== null) {
                        yield WidgetfyTheme::toHTML($embed, true) . "\n";
                        continue;
                    }
                }
                yield $line;
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

    /**
     * Reduce <object> tags of flash embed back to URL for some embed.
     * (e.g. Youtube).
     *
     * @param Generator $lines Generator of text lines to filter with.
     *
     * @return Generator
     */
    public static function reduceFlashEmbed(Generator $lines): Generator {
        $regex = '~^(.*?)<object( .*?|)\>(.*?<embed .*?src=[\'"](.+?)[\'"].*?>.*?)</object>(.*?)$~mi';
        return (function () use ($lines, $regex) {
            foreach ($lines as $line) {
                if (preg_match($regex, $line, $matches)) {
                    yield $matches[1] . $matches[4] . $matches[5];
                    continue;
                }
                yield $line;
            }
        })();
    }
}
