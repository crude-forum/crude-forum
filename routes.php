<?php

$router->addRoute('GET', '/post/{id:\d+}', function ($vars) {
    var_dump($vars);
    exit('post');
});

$router->addRoute('GET', '/post/{id}', function ($vars) {
    var_dump($vars);
    exit('post string id');
});