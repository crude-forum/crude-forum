<?php

include __DIR__ . '/../bootstrap.php';

use ywsing\CrudeForum\Paged;
use ywsing\CrudeForum\Filtered;
use ywsing\CrudeForum\PostSummary;
use ywsing\CrudeForum\Post;

$lang['author']='作者';
$lang['reply']='回覆';
$lang['forumIndex']='回論壇';


// a little configuration //
$cfgThreadTotal=10;

$lock = $forum->getLock();
$themePost = function(PostSummary $postSummary, Post $post) use ($lang) {
    return htmlspecialchars(
        '------------------<br/>[<a href="http://stupidsing.no-ip.org/sayForm.php?'.$postSummary->id.'">'.
        $lang['reply'].'</a>]&nbsp;&nbsp;'.
        '[<a href="http://stupidsing.no-ip.org/forum.php">'.$lang['forumIndex'].'</a>] <br/>------------------<br/>'.
        $lang['author'].': '.$postSummary->author.'<br/><br/>'.
        $post->htmlBody().
        '<br/>------------------<br/>[<a href="http://stupidsing.no-ip.org/sayForm.php?'.$postSummary->id.'">'.
        $lang['reply'].'</a>]&nbsp;&nbsp;'.
        '[<a href="http://stupidsing.no-ip.org/forum.php">'.$lang['forumIndex'].'</a>] <br/>'.
        '=============================='
    );
};

$mode = isset($_GET['mode']) ? $_GET['mode'] : NULL;

switch ($mode) {
default:
case 'post':
    $index = new Paged($forum->getIndex(), 0, $cfgThreadTotal);
    break;
case 'thread':
case 'threads':
    $index = new Paged(new Filtered($forum->getIndex(), function ($postSummary) {
        return ($postSummary->level == 0);
    }), 0, $cfgThreadTotal);
    break;
}

$postArr = array();
foreach ($index as $postSummary) {
    $post = $forum->readPost($postSummary->id);
    $postArr[]=array(
        'articleNo' => $postSummary->id,
        'subject' => $postSummary->title,
        'author' => $postSummary->author,
        'time' => $postSummary->time,
        'timestamp' => trim(preg_replace('/^(.+?) (.+?) (.+?) (\d\d)\:(\d\d):(\d\d) (\d\d\d\d)/', '$1, $3 $2 $7 $4:$5:$6 +0800', $postSummary->time)),
        'content' => $themePost($postSummary, $post),
    );
}

fclose ($lock);

header("Content-Type: text/xml; charset=utf-8");
echo '<?xml version="1.0"?>'."\n";
?>
<rss version="2.0">
	 <channel>
			<title>La sfolgorante idea...!!!</title>
			<link>http://stupidsing.no-ip.org/forum.php</link>
			<description></description>
			<language>zh-hk</language>
			<pubDate><?php print date('D, d M Y H:i:s ', time()); ?> GMT</pubDate>

			<lastBuildDate><?php print date('D, d M Y H:i:s ', time()); ?> GMT</lastBuildDate>
			<docs>http://blogs.law.harvard.edu/tech/rss</docs>
			<generator>Custom PHP by Koala Yeung</generator>
			<managingEditor>stupidsing@yahoo.com</managingEditor>
			<webMaster>stupidsing@yahoo.com</webMaster>
<?php foreach ((array) $postArr as $post): ?>
			<item>
				 <title><?php echo $post['subject']; ?></title>
				 <link>http://stupidsing.no-ip.org/read.php?<?php echo $post['articleNo']; ?></link>
				 <description><?php echo $post['content']; ?></description>
				 <pubDate><?php echo $post['timestamp']; ?></pubDate>
				 <guid>http://stupidsing.no-ip.org/read.php?<?php echo $post['articleNo']; ?></guid>
			</item>
<?php endforeach; ?>
	 </channel>
</rss>
