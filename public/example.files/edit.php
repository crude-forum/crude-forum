<?php

require __DIR__ . '/../../bootstrap.php';

use \CrudeForum\CrudeForum\Post;

Core::bootstrap(
	$dispatcher,
	$forum,
	Core::routeQueryString('/post', 'edit')
);