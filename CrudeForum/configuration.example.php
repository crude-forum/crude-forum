<?php

if (!defined('CRUDE_DIR')) {
    define('CRUDE_DIR', __DIR__);
    define('CRUDE_DIR_DATA', CRUDE_DIR . '/data/forumdata_utf8/');
    define('CRUDE_DIR_LOGS', CRUDE_DIR . '/data/logs');
}

$logDirectory = CRUDE_DIR_LOGS;
$dataDirectory = CRUDE_DIR_DATA;
$beginFormat = "";//"<body bgcolor=#111111 text=#777777 link=#AAAA00 vlink=#777777><font face=courier>";
$endFormat = "";//"</font></body>";
$administrator = "";
date_default_timezone_set ('Asia/Hong_Kong');

?>
