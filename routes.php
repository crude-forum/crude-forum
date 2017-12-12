<?php

use \FastRoute\RouteCollector;
use \ywsing\CrudeForum\Core;
use \ywsing\CrudeForum\Post;
use \ywsing\CrudeForum\PostSummary;
use \ywsing\CrudeForum\Iterator\Paged;
use \ywsing\CrudeForum\Iterator\Filtered;

$router->addRoute('GET', '/post/{postID:\d+}', function ($vars, $forum) use ($configs) {
    $lock = $forum->getLock();
    $postID = $vars['postID'];

    $forum->log("read?" . $postID);
    $post = $forum->readPost($postID);
    if ($post === NULL) die('post not found');

    echo $forum->template->render('post.twig', [
        'configs' => $configs,
        'linkHome' => 'index.html',
        'linkForumHome' => 'forum.php',
        'postID' => $postID,
        'post' => $post,
    ]);
});

$router->addRoute('GET', '/post/{postID:\d+}/prev', function ($vars, $forum) use ($configs) {
    $lock = $forum->getLock();
    $postID = $vars['postID'];
    try {
        $prev = $forum->readPrevPostSummary($postID);
        fclose($lock);
        header('Refresh: 0; URL=' . $forum->linkTo('post', $prev->id));
        echo $forum->template->render('base.twig', [
            'configs' => $configs,
        ]);
    } catch (Exception $e) {
        fclose($lock);
        die($e->getMessage());
    }
});

$router->addRoute('GET', '/post/{postID:\d+}/next', function ($vars, $forum) use ($configs) {
    $lock = $forum->getLock();
    $postID = $vars['postID'];
    try {
        $next = $forum->readNextPostSummary($postID);
        fclose($lock);
        header('Refresh: 0; URL=' . $forum->linkTo('post', $next->id));
        echo $forum->template->render('base.twig', [
            'configs' => $configs,
        ]);
    } catch (Exception $e) {
        fclose($lock);
        die($e->getMessage());
    }
});

$router->addRoute('GET', '/post/{postID:\d+}/back', function ($vars, $forum) use ($configs) {
    $lock = $forum->getLock();
    $postID = $vars['postID'];
    try {
        $postSummary = $forum->readPostSummary($postID);
        fclose($lock);
        header('Refresh: 0; URL=' . $forum->linkTo('forum', $configs['postPerPage'] * floor ($postSummary->pos / $configs['postPerPage'])));
        echo $forum->template->render('base.twig', [
            'configs' => configs,
        ]);
    } catch (Exception $e) {
        fclose($lock);
        die($e->getMessage());
    }
});

$showForm = function ($vars, $forum) use ($configs) {
    $postID = $vars['postID'] ?? '';
    $action = $vars['action'];

    if ($action == 'edit') {
        $post = $forum->readPost($postID);
        // TODO: read current post author from post
        // TODO: check if the post user is cookie user, or if user is admin
        $post->author = $post->header['author'] ?? '';
    } else if ($action == 'reply') {
        $parent = $forum->readPost($postID);
        $post = Post::replyFor($parent);
        $post->author = $_COOKIE['forumName'] ?? '';
        if ($parent == NULL) die('post not found');
    } else if ($action == 'add' && empty($postID)) {
        $post = new Post();
        $post->author = $_COOKIE['forumName'] ?? '';
    }

    echo $forum->template->render('postForm.twig', [
        'configs' => $configs,
        'action' => $action,
        'postID' => $postID,
        'post' => $post,
    ]);
};
$router->addRoute('GET', '/post/{postID:\d+}/{action:edit|reply}', $showForm);
$router->addRoute('GET', '/post/{action:add}', $showForm);

$savePost = function ($vars, $forum) use ($configs) {
    $forum->log("say?");
    $action = $vars['action'] ?? '';
    $postID = $vars['postID'] ?? FALSE;

    $author = stripslashes ($_POST["em_an"]);
    $title = stripslashes ($_POST["tcejbus"]);
    $body = stripslashes ($_POST["tx_et"]);
    $currentTime = strftime ("%a %F %T");

    // Characters to be avoided
    $author = str_replace(["\n", "\r", "\t"], ' ', $author);
    $title = str_replace (["\n", "\r", "\t"], ' ', $title);
    $title = str_replace ("\022", "'", $title);
    //$body = "來自：" . $author . "\n時間：" . $currentTime . "\n\n" . $body;
    $body = str_replace ("<", "&lt;", $body);
    $body = str_replace (">", "&gt;", $body);
    $body = str_replace ("\r\n", "\n", $body); // use UNIX linebreak
    if ($author <> '')
        setcookie ('forumName', $author, mktime (0, 0, 0, 1, 1, 2038), "/");

    // TODO: display error message with the originally filled form
    if (strlen($author) > 8) die('Name too long');
    if ($author == '') die('Please Enter your name');
    if ($title == '') die('Please Enter a subject');

    $lock = $forum->getLock();

    switch ($action) {
        case 'add':
            $nextID = $forum->getCount() + 1;
            $post = new Post(
                $title,
                $body,
                ['author' => $author, 'time' => $currentTime]
            );
            $forum->writePost($nextID, $post);
            $forum->appendIndex(new PostSummary(
                $nextID,
                0,
                $title,
                $author,
                $currentTime
            ), FALSE);
            $forum->incCount();
            fclose($lock);
            header('Refresh: 0; URL=' . $forum->linkTo('forum'));
            echo $forum->template->render('base.twig', [
                'configs' => $configs,
            ]);
            break;
        case 'reply':
            $parentID = $postID;
            $nextID = $forum->getCount() + 1;
            $post = new Post(
                $title,
                $body,
                ['author' => $author, 'time' => $currentTime]
            );
            $forum->writePost($nextID, $post);
            $forum->appendIndex(new PostSummary(
                $nextID,
                0,
                $title,
                $author,
                $currentTime
            ), $parentID);
            $forum->incCount();
            fclose($lock);
            header('Refresh: 0; URL=' . $forum->linkTo('post', $parentID, 'back'));
            echo $forum->template->render('base.twig', [
                'configs' => $configs,
            ]);
            break;
        case 'edit':
            $existingPost = $forum->readPost($postID);
            // check if the editor is the original author
            if (!$forum->isAdmin($author) && ($existingPost->header['author'] !== $author)) {
                throw new \Exception('you are not the original author of this post.');
            }

            // inherit header by default
            $header = $existingPost->header;
            if ($forum->isAdmin($author)) {
                // admin can override user name
                $header['author'] = $author;
            }

            // generate new post
            $post = new Post(
                $title,
                $body,
                $header
            );
            $forum->writePost($postID, $post);
            fclose($lock);
            header('Refresh: 0; URL=' . $forum->linkTo('post', $postID));
            echo $forum->template->render('base.twig', [
                'configs' => $configs,
            ]);
            break;
    }
};
$router->addRoute('POST', '/post/{postID:\d+}/{action:edit|reply}', $savePost);
$router->addRoute('POST', '/post/{action:add}', $savePost);

$router->addRoute('GET', '/forum[/[{page:\d+}]]', function ($vars, $forum) use ($configs) {

    $page = $vars['page'] ?? 0;
    $forum->log("forum?" . $page);

    $lock = $forum->getLock();
    $index = new Paged($forum->getIndex(), $page, $configs['postPerPage']);
    $contents = $forum->template->render('forum.twig', array(
        'configs' => $configs,
        'page' => $page,
        'linkHome' => 'index.html',
        'linkForumHome' => $forum->linkTo('forum'),
        'linkPrev' => $forum->linkTo('forum', (($page > $configs['postPerPage']) ? $page - $configs['postPerPage'] : 0)),
        'linkNext' => $forum->linkTo('forum', ($page + $configs['postPerPage'])),
        'linkSay' => $forum->linkTo('post', NULL, 'add'),
        'postSummaries' => $index,
    ));
    fclose ($lock);
    echo $contents;
});

$router->addRoute('GET', '/rss', function ($vars, $forum) use ($configs) {
    $mode = $_GET['mode'] ?? 'post';

    // read post summaries as reference to the mode
    $lock = $forum->getLock();
    switch ($mode) {
        case 'thread':
        case 'threads':
            $postSummaries  = new Paged(
                new Filtered(
                    $forum->getIndex(),
                    function ($postSummary) {
                        return ($postSummary->level == 0);
                    }
                ),
                0, $configs['rssPostLimit']
            );
            break;
        case 'post':
            $postSummaries = new Paged(
                $forum->getIndex(),
                0, $configs['rssPostLimit']
            );
            break;
        default:
            throw new \Exception(sprintf('mode "%s" is not supported',
                htmlspecialchars($mode)));
    }

    // read post index and post contents
    $post = [];
    foreach ($postSummaries as $postSummary) {
        $post = $postSummary;
        $post->body = $forum->template->render('rssPostBody.twig', [
            'configs' => $configs,
            'sitePath' => 'http://localhost:8080',
            'postSummary' => $postSummary,
            'post' => $forum->readPost($postSummary->id),
            't' => [
                'author' => '作者',
                'reply' => '回覆',
                'forumIndex' => '回論壇',
            ],
        ]);
        $posts[] = $post;
    }
    unset($postSummaries);
    fclose($lock);

    // render rss
    echo $forum->template->render('rss.twig', [
        'configs' => $configs,
        'link' => $forum->linkTo('forum', NULL, NULL, TRUE),
        'language' => 'zh-hk',
        'pubDate' => date('D, d M Y H:i:s ', time()),
        'lastBuildDate' => date('D, d M Y H:i:s ', time()),
        'generator' => 'Custom PHP by Koala Yeung',
        'managingEditor' => 'stupidsing@yahoo.com (Y W Sing)',
        'webMaster' => 'stupidsing@yahoo.com (Y W Sing)',
        'posts' => $posts,
    ]);
});