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
use \Fusonic\OpenGraph\Consumer;
use \GuzzleHttp\Client as HttpClient;
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
            $filter = static::pipe($filters);
            return static::pipeToString(
                $filter(
                    static::stringToPipe($string)
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
     * @param CacheInterface $cache ?CacheInterface of PHP Cache
     * @param array     $options Options for Widgetfy::translate function
     *
     * @return function (:Generator) :Generator
     */
    public static function autoWidgetfy($cache=null, array $options=[]): Callable
    {
        $regex = '~^((?<![="\'])(https?)://([^\s<]+)|(?<!\/)(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])$~i';
        return function (Generator $lines) use ($regex, $cache, $options): Generator
        {
            return (function () use ($lines, $regex, $cache, $options)
            {
                $ogConsumer = new Consumer();
                foreach ($lines as $line) {
                    if (preg_match($regex, trim($line), $matches)) {
                        $url = $matches[1];
                        if (($embed = Widgetfy::translate($url)) !== null) {
                            yield WidgetfyTheme::toHTML($embed, true) . "\n";
                            continue;
                        }

                        $cacheItem = null;
                        $og = null;

                        if ($cache != null) {
                            // if cache system in-place
                            $cacheKey = 'opengraph__' . rtrim(str_replace(['+', '-', '/'], '_', base64_encode($url)), '=');
                            $cacheItem = $cache->getItem($cacheKey);
                            $cacheContent = $cacheItem->get();
                            if ($cacheContent != null) {
                                $og = json_decode($cacheContent);
                            }
                        }

                        if ($og === null) {

                            // load the URL opengraph information
                            try {
                                $ogConsumer->useFallbackMode = true; // fallback to HTML title
                                $client = new HttpClient();
                                $response = $client->get($url);
                                $og = $ogConsumer->loadHtml($response->getBody()->__toString(), $url);

                                // if there is no opengraph error
                                if ($cacheItem != null) {
                                    // if cache system in-place
                                    $cacheItem->set(json_encode($og));
                                    $cache->save($cacheItem);
                                }

                            } catch (\Exception $e) {

                                // if it has not getting any proper response
                                // simply yield the url
                                if (!isset($response)) {
                                    yield $url;
                                    continue;
                                }

                                $og = new \StdClass();
                                $html = $response->getBody()->__toString();
                                $og->url = $url;
                                if (preg_match_all('~<title>(.+?)</title>~', $html, $set_matches, PREG_SET_ORDER)) {
                                    if (!empty($set_matches)) {
                                        $og->title = $set_matches[0][1];
                                    }
                                }
                                if (preg_match_all('~<meta property="og:image".+?content="(.+?)".+?/>~', $html, $set_matches, PREG_SET_ORDER)) {
                                    if (!empty($set_matches)) {
                                        $image = new \StdClass();
                                        $image->url = $set_matches[0][1];
                                        $og->images[] = $image;
                                    }
                                }
                                if (preg_match_all('~<link rel="icon".+?href="(.+?)".+?>~', $html, $set_matches, PREG_SET_ORDER)) {
                                    if (!empty($set_matches)) {
                                        $image = new \StdClass();
                                        $image->url = $set_matches[0][1];
                                        if (!preg_match('~^(http|https)://~', $image->url)) {
                                            $parsed_url = parse_url($url);
                                            if (!preg_match('~/$~', $parsed_url['path'])) {
                                                $parsed_url['path'] = dirname($parsed_url['path']) . '/';
                                            }
                                            $path = ($image->url[0] === '/') ? $image->url : $parsed_url['path'];
                                            $image->url = "{$parsed_url['scheme']}://{$parsed_url['host']}{$path}";
                                        }
                                        $og->images[] = $image;
                                    }
                                }

                                if (preg_match_all('~<meta name="description".+?content="(.+?)".+?/>~im', $html, $set_matches, PREG_SET_ORDER)) {
                                    if (!empty($set_matches)) {
                                        $og->description = $set_matches[0][1];
                                    }
                                }
                                if (!isset($og->title) || !isset($og->images)) {
                                    // if no valid og tags
                                    $og = [ 'notValid' => true, ];
                                    $cacheItem->set(json_encode($og));
                                    $cache->save($cacheItem);
                                    yield $url;
                                    continue;
                                }
                                if ($cacheItem != null) {
                                    // if cache system in-place
                                    $cacheItem->set(json_encode($og));
                                    $cache->save($cacheItem);
                                }
                            }
                        }

                        // parse opengraph item
                        if (isset($og->notValid) && $og->notValid === true) {

                            // if not a valid og target, simply retury the url
                            yield $url;
                            continue;

                        } else if (isset($og->title) && !empty($og->title) && isset($og->images) && !empty($og->images)) {
                            // theme this like a widget
                            $href = $og->url ?? $url;
                            $host = parse_url($href)['host'] ?? '';
                            yield sprintf(
                                '<figure class="og-widget-wrapper">'.
                                    '<a class="og-widget" target="_blank" href="%s">'.
                                        '<img src="%s" />'.
                                        '<figcaption>'.
                                            '<div class="title">%s</div>'.
                                            '<div class="desc">%s</div>'.
                                            '<div class="host">%s</div>'.
                                        '</figcaption>'.
                                    '</a>'.
                                '</figure>',
                                $href,
                                $og->images[0]->url,
                                htmlspecialchars($og->title),
                                str_replace(["\r\n", "\n"], "", strip_tags($og->description ?? '')),
                                preg_replace('/^www\./', '', $host)
                            );
                            continue;
                        }
                    }
                    yield $line;
                }
            })();
        };
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
