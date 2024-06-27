<?php

use \CrudeForum\CrudeForum\Core;

require __DIR__ . '/../bootstrap.php';

// bootstrap with given default forum core, dispatcher and route callback
Core::bootstrap(
    $container,
    Core::routeHome('/forum', Core::routeURI()),
);