<?php

use \CrudeForum\CrudeForum\Core;

require __DIR__ . '/../../bootstrap.php';

// hard code all paths to start with '/temp/forum.php/'
$forum->setBasePath('/example.pathinfo/forum.php');
$forum->setBaseURL('http://localhost:8080/example.pathinfo/forum.php');

// render route
Core::bootstrap(
    $container,
    Core::routeHome('/forum', Core::routePathInfo())
);