<?php
include __DIR__ . '/../bootstrap.php';

use ywsing\CrudeForum\Post;

Core::bootstrap(
	$dispatcher,
	$forum,
	Core::routeQueryString('/post', 'edit')
);