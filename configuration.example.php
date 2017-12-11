<?php

if (!defined('CRUDE_DIR')) {
    define('CRUDE_DIR', __DIR__);
    define('CRUDE_DIR_DATA', CRUDE_DIR . '/data/forumdata_utf8');
    define('CRUDE_DIR_LOGS', CRUDE_DIR . '/data/logs');
    define('CRUDE_ADMIN', '');
}

$logDirectory = CRUDE_DIR_LOGS;
$dataDirectory = CRUDE_DIR_DATA;
$beginFormat = "";//"<body bgcolor=#111111 text=#777777 link=#AAAA00 vlink=#777777><font face=courier>";
$endFormat = "";//"</font></body>";
$administrator = CRUDE_ADMIN;
date_default_timezone_set ('Asia/Hong_Kong');

?>
