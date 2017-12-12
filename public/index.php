<?php

require __DIR__ . '/../bootstrap.php';

use \ywsing\CrudeForum\Core;

// bootstrap with given default forum core, dispatcher and route callback
Core::bootstrap(
    $dispatcher,
    $forum,
    '\ywsing\CrudeForum\Core::routeURI'
);