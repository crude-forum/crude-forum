<?php

use \CrudeForum\CrudeForum\Core;

require __DIR__ . '/../../bootstrap.php';

$action = empty(trim($_SERVER['QUERY_STRING'])) ? 'add' : 'reply';
Core::bootstrap(
  $container,
  Core::routeQueryString('/post', $action)
);