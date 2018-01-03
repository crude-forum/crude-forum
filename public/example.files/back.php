<?php

require __DIR__ . '/../../bootstrap.php';

use \CrudeForum\CrudeForum\Core;

Core::bootstrap(
    $dispatcher,
    $forum,
    Core::routeQueryString('/post', 'back')
);