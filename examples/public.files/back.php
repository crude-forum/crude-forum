<?php

use \CrudeForum\CrudeForum\Core;

require __DIR__ . '/../../bootstrap.php';

Core::bootstrap(
    $container,
    Core::routeQueryString('/post', 'back')
);