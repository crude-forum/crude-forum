<?php

use ywsing\CrudeForum\Core;
use ywsing\CrudeForum\Iterator\Paged;

$postPerPage = 100;

$router->addRoute('GET', '/post/{postID:\d+}', function ($vars, $forum) {
    $lock = $forum->getLock();
    $postID = $vars['postID'];

    $forum->log("read?" . $postID);
    $post = $forum->readPost($postID);
    if ($post === NULL) die('post not found');

    echo $forum->template->render('post.twig', array(
        'linkHome' => 'index.html',
        'linkForumHome' => 'forum.php',
        'postID' => $postID,
        'post' => $post,
    ));
});

$router->addRoute('GET', '/post/{postID:\d+}/prev', function ($vars, $forum) {
    $lock = $forum->getLock();
    $postID = $vars['postID'];
    try {
        $prev = $forum->readPrevPostSummary($postID);
        fclose($lock);
        header('Refresh: 0; URL=' . Core::linkTo('post', $prev->id));
    } catch (Exception $e) {
        fclose($lock);
        die($e->getMessage());
    }
});

$router->addRoute('GET', '/post/{postID:\d+}/next', function ($vars, $forum) {
    $lock = $forum->getLock();
    $postID = $vars['postID'];
    try {
        $next = $forum->readNextPostSummary($postID);
        fclose($lock);
        header('Refresh: 0; URL=' . Core::linkTo('post', $next->id));
    } catch (Exception $e) {
        fclose($lock);
        die($e->getMessage());
    }
});

$router->addRoute('GET', '/post/{postID:\d+}/back', function ($vars, $forum) use ($postPerPage) {
    $lock = $forum->getLock();
    $postID = $vars['postID'];
    try {
        $postSummary = $forum->readPostSummary($postID);
        fclose($lock);
        header('Refresh: 0; URL=' . Core::linkTo('forum', $postPerPage * floor ($postSummary->pos / $postPerPage)));
    } catch (Exception $e) {
        fclose($lock);
        die($e->getMessage());
    }
});

$router->addRoute('GET', '/forum[/[{page:\d+}]]', function ($vars, $forum) use ($postPerPage) {

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
        'linkSay' => Core::linkTo('forum', '', 'add'),
        'postSummaries' => $index,
    ));
    fclose ($lock);
    echo $contents;
});
