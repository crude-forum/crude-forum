<?php

/**
 * Example configuration file
 *
 * PHP Version 7.1
 *
 * @category Configuration
 * @package  None
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/blob/master/app/configuration.example.php
 * Source Code
 */

// base install folder for Crude Forum
define('CRUDE_DIR', __DIR__);

// directory to store post, index and other data
define('CRUDE_DIR_DATA', CRUDE_DIR . '/data/forumdata_utf8');

// directory to store logs
define('CRUDE_DIR_LOGS', CRUDE_DIR . '/data/logs');

// directory to caches
define('CRUDE_DIR_CACHE', CRUDE_DIR . '/data/cache');

// user name of the administrator
// will be checked if you can edit other people's post
// and be invisible to logs
define('CRUDE_ADMIN', '');

// site name
define('CRUDE_SITE_NAME', 'Wandering and wonderings');

// slogan on top of forum index page
define('CRUDE_SLOGAN_TOP', '心意就如密友，長路裏相伴漫遊。');

// slogan on top of forum index page
define('CRUDE_SLOGAN_BOTTOM', '漫長漫長夜間，我伴我閒談；漫長漫長夜晚，從未覺是冷。');

// number of post per page in forum index page
define('CRUDE_POST_PER_PAGE', 100);

// number of post displayed in rss feed
define('CRUDE_RSS_POST_NUMBER', 10);

// full URL to the base URL of the site
// define('CRUDE_BASE_URL', 'http://localhost:8080');

// path to the forum
// define('CRUDE_BASE_PATH', '/forum');     // for REQUEST_URI routing (default), or
// define('CRUDE_BASE_PATH', '/forum.php'); // for PATH_INFO routing

// path to the CSS file
// define('CRUDE_ASSETS_PATH', '/forum/assets/');
