<?php

include __DIR__ . '/../bootstrap.php';

use ywsing\CrudeForum\Iterator\Paged;

$page = $_SERVER['QUERY_STRING'] ?? 0;
if ((int) $page < 0) $page = 0;
$postPerPage = 100;

$forum->log("forum?" . $page);

?>

<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel="alternate" type="application/rss+xml" title="Forum Threads - RSS" href="https://stupidsing.no-ip.org/rss.php?mode=thread" />
<link rel="alternate" type="application/rss+xml" title="Forum Posts - RSS" href="https://stupidsing.no-ip.org/rss.php?mode=post" />
<link rel=stylesheet href=forum.css>

<?php print $beginFormat; ?>

<title>Wandering and wonderings</title>
<table width=100%><tr><td><div class=buttons><a href=sayForm.php>發言</a>&nbsp;&nbsp;<a href=
forum.php?<?php if($page > $postPerPage) print $page - $postPerPage; else print '0'; ?>
>上一頁</a>&nbsp;&nbsp;<a href=forum.php>首頁</a>&nbsp;&nbsp;<a href=index.html>主頁</a></div></td>
<td align=right><div class=text>心意就如密友，長路裏相伴漫遊。</div></td></tr></table><br>

<hr><br><br><div class=miscs>

<?php
$lock = $forum->getLock();
$index = new Paged($forum->getIndex(), $page, $postPerPage);
foreach ($index as $postSummary) {
    print str_repeat("　 ", $postSummary->level);
    print "<a href=\"read.php?{$postSummary->id}\">{$postSummary->title}</a>";
    print ($postSummary->level = 0) ? "　　　 -- " : "　　-- ";
    print $postSummary->author . " at " . $postSummary->time . "<br>";
}
fclose ($lock);
?>

</div><br><br><hr><br>
<table width=100%><tr align=top><td><div class=buttons><a href=sayForm.php>發言</a>&nbsp;&nbsp;<a href=
forum.php?<?php print ((int) $page) + $postPerPage; ?>
>下一頁</a>&nbsp;&nbsp;<a href=forum.php>首頁</a>&nbsp;&nbsp;<a href=../index.html>回主頁</a></div></td><td align=right>漫長漫長夜間，我伴我閒談；漫長漫長夜晚，從未覺是冷。</td></tr></table>

<?php print $endFormat; ?>
