<?php

use ywsing\CrudeForum\Core;
use ywsing\CrudeForum\Iterator\Paged;

$router->addRoute('GET', '/post/{postID:\d+}', function ($vars, $forum) {
    $lock = $forum->getLock();
    $postID = $vars['postID'] ?? '';

    if (empty(trim($postID))) die('empty postID');
    $forum->log("read?" . $postID);
    $post = $forum->readPost($postID);
    if ($post === NULL) die('post not found');

    echo $forum->template->render('post.twig', array(
        'linkHome' => 'index.html',
        'linkForumHome' => 'forum.php',
        'postID' => $postID,
        'linkPrev' => Core::linkTo('post/prev', $postID),
        'linkNext' => Core::linkTo('post/next', $postID),
        'linkBack' => Core::linkTo('forum/back', $postID),
        'post' => $post,
    ));
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
        'linkForumHome' => Core::linkTo('forum'),
        'linkPrev' => Core::linkTo('forum', (($page > $postPerPage) ? $page - $postPerPage : 0)),
        'linkNext' => Core::linkTo('forum', ($page + $postPerPage)),
        'linkSay' => Core::linkTo('forum/add'),
        'postSummaries' => $index,
    ));
    fclose ($lock);
    echo $contents;
});
