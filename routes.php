<?php

use ywsing\CrudeForum\Iterator\Paged;

$router->addRoute('GET', '/post/{id:\d+}', function ($vars) {
    var_dump($vars);
    exit('post');
});

$router->addRoute('GET', '/post/{id}', function ($vars) {
    var_dump($vars);
    exit('post string id');
});

$router->addRoute('GET', '/forum[/[{page:\d+}]]', function ($vars, $forum) {

    $postPerPage = 100;
    $page = $vars['page'] ?? 0;
    $forum->log("forum?" . $page);

    $lock = $forum->getLock();
    $index = new Paged($forum->getIndex(), $page, $postPerPage);
    $contents = $forum->template->render('forum.twig', array(
        'page' => $page,
        'linkHome' => 'index.html',
        'linkForumHome' => 'forum.php' ,
        'linkPrev' => 'forum.php?' . (($page > $postPerPage) ? $page - $postPerPage : 0),
        'linkNext' => 'forum.php?' . ($page + $postPerPage),
        'linkSay' => 'sayForm.php',
        'postSummaries' => $index,
    ));
    fclose ($lock);
    echo $contents;
});
