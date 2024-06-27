<?php

/**
 * Bootstrapping the main objects to use in the forum
 *
 * PHP Version 8.1
 *
 * @file     bootstrap.php
 * @category File
 * @package  None
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/bootstrap.php Source Code
 */

// common bootstrap code
require_once __DIR__ . '/vendor/autoload.php';

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use CrudeForum\CrudeForum\Core;
use CrudeForum\CrudeForum\Storage\FileStorage;
use CrudeForum\CrudeForum\StreamFilter;
use DI\ContainerBuilder;
use FastRoute\Dispatcher;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;

// load env config
Core::loadDotenv(__DIR__);

// for debug
if ((bool) Core::env('CRUDE_DEBUG', 'FALSE')) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// set default timezone
date_default_timezone_set(Core::env('CRUDE_TIMEZONE', 'UTC'));

// Build the container in a closure to elimiate global variables
$container = (function (): ContainerBuilder {

    // Use ad DI container to build the environment
    $builder = new ContainerBuilder();

    $builder->addDefinitions([

        'storage' => function () {
            return new FileStorage(
                logDirectory: Core::env('CRUDE_DIR_LOGS', __DIR__ . '/data/logs'),
                dataDirectory: Core::env('CRUDE_DIR_DATA', __DIR__ . '/data/forumdata_utf8'),
            );
        },

        'cache' => function () {
            $cache = null;
            if (!empty(Core::env('CRUDE_DIR_CACHE'))) {
                $fsAdapter = new Local(Core::env('CRUDE_DIR_CACHE') . '/common');
                $fs = new Filesystem($fsAdapter);
                $cache = new FilesystemCachePool($fs);
            }
            return $cache;
        },

        'twig' => function (ContainerInterface $container) {
            /** @var AbstractCachePool */
            $cache = $container->get('cache');
            $twig = new \Twig\Environment(
                new \Twig\Loader\FilesystemLoader(
                    [
                        __DIR__ . '/views/custom',
                        __DIR__ . '/views/dist',
                    ]
                ),
                [
                    'cache' =>
                        (($cache_dir = Core::env('CRUDE_DIR_CACHE')) === null) ? false : $cache_dir . '/twig',
                ]
            );

            // define body-to-html filter
            $bodyToHTML = new \Twig\TwigFilter('bodyToHTML', function ($string) use ($cache) {
                $filter = StreamFilter::pipeString(
                    StreamFilter::quoteToBlockquote(...),
                    StreamFilter::reduceFlashEmbed(...),
                    StreamFilter::autoWidgetfy($cache, [])(...),
                    StreamFilter::autoLink(...),
                    StreamFilter::autoParagraph(...),
                );

                // concat filtered lines back into string
                // and do auto br
                return nl2br($filter($string), false);
            });
            $twig->addFilter($bodyToHTML);

            // define linkTo filter
            $link = new \Twig\TwigFilter('link', function ($id, $type, $action=null) {
                global $forum;
                return $forum->linkTo($type, $id, $action);
            });
            $twig->addFilter($link);

            return $twig;
        },

        'forum' => function (ContainerInterface $container) {
            /** @var FileStorage */
            $storage = $container->get('storage');

            /** @var \Twig\Environment */
            $twig = $container->get('twig');

            // initialize forum core
            return new Core(
                $storage,
                $twig,
                [
                    'administrator' => Core::env('CRUDE_ADMIN'),
                    'baseURL' => Core::env('CRUDE_BASE_URL', Core::defaultBaseURL()),
                    'basePath' => Core::env('CRUDE_BASE_PATH', Core::defaultBasePath()),
                ]
            );
        },

        'configs' => function () {
            // The 'configs' array used in template rendering.
            // Used in routes.php and views
            return [
                'formPostAuthor' => Core::env('CRUDE_FORM_POST_AUTHOR'),
                'formPostTitle' => Core::env('CRUDE_FORM_POST_TITLE'),
                'formPostBody' => Core::env('CRUDE_FORM_POST_BODY'),
                'postPerPage' => (int) Core::env('CRUDE_POST_PER_PAGE'),
                'rssPostLimit' => (int) Core::env('CRUDE_RSS_POST_NUMBER'),
                'siteName' => Core::env('CRUDE_SITE_NAME'),
                'sloganTop' => Core::env('CRUDE_SLOGAN_TOP'),
                'sloganBottom' => Core::env('CRUDE_SLOGAN_BOTTOM'),
                'baseURL' => Core::env('CRUDE_BASE_URL'),
                'basePath' => Core::env('CRUDE_BASE_PATH'),
                'assetsPath' => Core::env('CRUDE_ASSETS_PATH'),
            ];
        },

        'dispatcher' => function (ContainerInterface $container) {
            $configs = $container->get('configs');
            return FastRoute\simpleDispatcher(
                function (FastRoute\RouteCollector $router) use ($configs) {
                    // use routes defined in routes.php
                    include __DIR__ . '/routes.php';
                }
            );
        },
    ]);

    return $builder;
})()->build();

// Build the essential global variables from container

/** @var Dispatcher */
$dispatcher = $container->get('dispatcher');

/** @var Core */
$forum = $container->get('forum');

/** @var array */
$configs = $container->get('configs');
