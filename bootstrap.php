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

use Cache\Adapter\Filesystem\FilesystemCachePool;
use CrudeForum\CrudeForum\Config;
use CrudeForum\CrudeForum\Core;
use CrudeForum\CrudeForum\Storage;
use CrudeForum\CrudeForum\Storage\FileStorage;
use CrudeForum\CrudeForum\StreamFilter;
use DI\Container;
use DI\ContainerBuilder;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Environment;

// Build the container in a closure to elimiate global variables
$container = (function (): ContainerBuilder {

    // Setup that has to be done before setting up container
    // i.e. load env config, setup display errors and timezone.

    // load env configs
    Core::loadDotenv(__DIR__);
    $configs = new Config(
        formNamePostAuthor: Core::env('CRUDE_FORM_POST_AUTHOR'),
        formNamePostTitle: Core::env('CRUDE_FORM_POST_TITLE'),
        formNamePostBody: Core::env('CRUDE_FORM_POST_BODY'),
        postPerPage: (int) Core::env('CRUDE_POST_PER_PAGE'),
        rssPostLimit: (int) Core::env('CRUDE_RSS_POST_NUMBER'),
        siteName: Core::env('CRUDE_SITE_NAME'),
        sloganTop: Core::env('CRUDE_SLOGAN_TOP'),
        sloganBottom: Core::env('CRUDE_SLOGAN_BOTTOM'),
        baseURL: Core::env('CRUDE_BASE_URL', Core::defaultBaseURL()),
        basePath: Core::env('CRUDE_BASE_PATH', Core::defaultBasePath()),
        assetsPath: Core::env('CRUDE_ASSETS_PATH'),
        timezone: Core::env('CRUDE_TIMEZONE', 'UTC'),
        debug: (bool) Core::env('CRUDE_DEBUG', 'FALSE'),
    );

    // TODO: should have better solution than this
    $administrator = Core::env('CRUDE_ADMIN');

    // for debug
    if ($configs->debug) {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    }

    // set default timezone
    date_default_timezone_set($configs->timezone);

    // Use ad DI container to build the environment
    $builder = new ContainerBuilder();

    $builder->addDefinitions([

        'storage' => DI\get(Storage::class),
        Storage::class => function () {
            return new FileStorage(
                logDirectory: Core::env('CRUDE_DIR_LOGS', __DIR__ . '/data/logs'),
                dataDirectory: Core::env('CRUDE_DIR_DATA', __DIR__ . '/data/forumdata_utf8'),
            );
        },

        'cache' => DI\get(CacheItemPoolInterface::class),
        CacheItemPoolInterface::class => function () {
            $cache = null;
            if (!empty(Core::env('CRUDE_DIR_CACHE'))) {
                $fsAdapter = new Local(Core::env('CRUDE_DIR_CACHE') . '/common');
                $fs = new Filesystem($fsAdapter);
                $cache = new FilesystemCachePool($fs);
            }
            return $cache;
        },

        'twig' => DI\get(Environment::class),
        Environment::class => function (ContainerInterface $container) {
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
            $bodyToHTML = new \Twig\TwigFilter('bodyToHTML', function ($string) use ($container) {

                /** @var CacheItemPoolInterface */
                $cache = $container->get('cache');

                $filter = StreamFilter::pipeString(
                    StreamFilter::quoteToBlockquote(...),
                    StreamFilter::reduceFlashEmbed(...),
                    StreamFilter::autoWidgetfy($cache)(...),
                    StreamFilter::autoLink(...),
                    StreamFilter::autoParagraph(...),
                );

                // concat filtered lines back into string
                // and do auto br
                return nl2br($filter($string), false);
            });
            $twig->addFilter($bodyToHTML);

            // define "link" filter, a short cut to "linkTo"
            // `userId | link('user', 'action')` equals to `linkTo('user', userId, 'action')`
            $link = new \Twig\TwigFilter('link', function ($id, $type, $action=null) use ($container) {
                /** @var Core */
                $forum = $container->get('forum');
                return $forum->linkTo($type, $id, $action);
            });
            $twig->addFilter($link);

            // Add configs as global variable
            $twig->addGlobal('configs', $container->get('configs'));

            return $twig;
        },

        'forum' => DI\get(Core::class),
        Core::class => function (ContainerInterface $container) use ($administrator) {
            /** @var FileStorage */
            $storage = $container->get('storage');

            /** @var \Twig\Environment */
            $twig = $container->get('twig');

            /** @var Config */
            $configs = $container->get('configs');

            // initialize forum core
            return new Core(
                $storage,
                $twig,
                $configs,
                ['administrator' => $administrator]
            );
        },

        'configs' => DI\get(Config::class),
        Config::class => $configs,

        'dispatcher' => DI\get(Dispatcher::class),
        Dispatcher::class => function () {
            // use routes defined in routes.php
            return FastRoute\simpleDispatcher(fn(RouteCollector $router)  => include __DIR__ . '/routes.php');
        },
    ]);

    return $builder;
})()->build();
