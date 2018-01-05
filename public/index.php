<?php

require __DIR__ . '/../bootstrap.php';

use \CrudeForum\CrudeForum\Core;

// bootstrap with given default forum core, dispatcher and route callback
Core::bootstrap(
    $dispatcher,
    $forum,
    Core::routeHome('/forum', Core::routeURI()),
    $configs
);