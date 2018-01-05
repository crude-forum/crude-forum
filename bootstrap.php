<?php

/**
 * Bootstrapping the main objects to use in the forum
 *
 * PHP Version 7.1
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

// load env config
use \CrudeForum\CrudeForum\Core;
Core::loadDotenv(__DIR__);

// for debug
if ((bool) Core::env('CRUDE_DEBUG', 'FALSE')) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// set default timezone
date_default_timezone_set(Core::env('CRUDE_TIMEZONE', 'UTC'));

// initialize forum storage
$storage = new \CrudeForum\CrudeForum\Storage\FileStorage(
    [
        'logDirectory' => Core::env('CRUDE_DIR_LOGS', __DIR__ . '/data/logs'),
        'dataDirectory' => Core::env('CRUDE_DIR_DATA', __DIR__ . '/data/forumdata_utf8'),
    ]
);

// initialize template engine
$template = new \Twig\Environment(
    new \Twig\Loader\FilesystemLoader(__DIR__ . '/views'),
    [
        'cache' =>
            (($cache_dir = Core::env('CRUDE_DIR_CACHE')) === null) ? false : $cache_dir . '/twig',
    ]
);

// initialize forum core
$forum = new Core(
    $storage,
    $template,
    [
        'administrator' => Core::env('CRUDE_ADMIN'),
        'baseURL' => Core::env('CRUDE_BASE_URL', Core::defaultBaseURL()),
        'basePath' => Core::env('CRUDE_BASE_PATH', Core::defaultBasePath()),
    ]
);

// some configurations in routes and template rendering
$configs = [
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

// initialize route dispatcher
$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $router) use ($configs) {
        // use routes defined in routes.php
        include __DIR__ . '/routes.php';
    }
);
