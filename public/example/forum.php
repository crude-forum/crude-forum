<?php

require __DIR__ . '/../../bootstrap.php';

use ywsing\CrudeForum\Core;

// hard code all paths to start with '/temp/forum.php/'
$forum->setBasePath('/example/forum.php');
$forum->setBaseURL('http://localhost:8080/example/forum.php');

// render route
Core::bootstrap(
    $dispatcher,
    $forum,
    Core::routeHome('/forum', Core::routePathInfo())
);