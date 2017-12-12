<?php

// for debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// common bootstrap code
require_once __DIR__ . '/configuration.php';
require_once __DIR__ . '/vendor/autoload.php';

// default paths
if (!defined('CRUDE_BASE_URL')) define('CRUDE_BASE_URL', \ywsing\CrudeForum\Core::defaultBaseURL());
if (!defined('CRUDE_BASE_PATH')) define('CRUDE_BASE_PATH', \ywsing\CrudeForum\Core::defaultBasePath());
if (!defined('CRUDE_CSS_PATH')) define('CRUDE_CSS_PATH', CRUDE_BASE_PATH . '/assets/forum.css');
if (!defined('CRUDE_ROUTING')) define('CRUDE_ROUTING', 'REQUEST_URI');

// set default timezone
date_default_timezone_set('Asia/Hong_Kong');

// initialize forum core
$forum = new \ywsing\CrudeForum\Core([
    'logDirectory' => CRUDE_DIR_LOGS,
    'dataDirectory' => CRUDE_DIR_DATA,
    'cacheDirectory' => CRUDE_DIR_CACHE,
    'administrator' => CRUDE_ADMIN,
    'baseURL' => CRUDE_BASE_URL,
    'basePath' => CRUDE_BASE_PATH,
    'routing' => CRUDE_ROUTING,
]);

// initialize route dispatcher
$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $router) {

    // some configurations in routes and template rendering
    $configs = [
        'postPerPage' => CRUDE_POST_PER_PAGE,
        'rssPostLimit' => CRUDE_RSS_POST_NUMBER,
        'siteName' => CRUDE_SITE_NAME,
        'sloganTop' => CRUDE_SLOGAN_TOP,
        'sloganBottom' => CRUDE_SLOGAN_BOTTOM,
        'baseURL' => CRUDE_BASE_URL,
        'basePath' => CRUDE_BASE_PATH,
        'cssPath' => CRUDE_CSS_PATH,
    ];

    // use routes defined in routes.php
    require __DIR__ . '/routes.php';
});
