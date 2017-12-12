<?php
include __DIR__ . '/CrudeForum/bootstrap.php';
include __DIR__ . '/../bootstrap.php';

use \ywsing\CrudeForum\Core;

Core::bootstrap(
  $dispatcher,
  $forum,
  Core::routeQueryString('/post', 'edit')
);