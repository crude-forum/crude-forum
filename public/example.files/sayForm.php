<?php

require __DIR__ . '/../../bootstrap.php';

use ywsing\CrudeForum\Core;

Core::bootstrap(
  $dispatcher,
  $forum,
  Core::routeQueryString('/post', 'add')
);