<?php

include __DIR__ . '/CrudeForum/bootstrap.php';

$postID = getenv ("QUERY_STRING");
$forum->log("read?" . $postID);
$post = $forum->readPost($postID);

?>

<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel=stylesheet href=forum.css>

<?php print $beginFormat; ?>

<title><?php echo $post->title; ?></title>
<div class=text><?php echo $post->title; ?></div><br><br><br><div class=buttons>
<a href=sayForm.php?<?php echo $postID; ?>>回覆</a>&nbsp;
<a href=prev.php?<?php echo $postID; ?>>上一發言</a>&nbsp;
<a href=next.php?<?php echo $postID; ?>>下一發言</a>&nbsp;
<a href=back.php?<?php echo $postID; ?>>回論壇</a><br><br>
</div><hr><br><div class=text>

<?php echo $post->htmlBody(); ?>

</div><br><hr><br><div class=buttons>
<a href=sayForm.php?<?php echo $postID; ?>>回覆</a>&nbsp;
<a href=prev.php?<?php echo $postID; ?>>上一發言</a>&nbsp;
<a href=next.php?<?php echo $postID; ?>>下一發言</a>&nbsp;
<a href=back.php?<?php echo $postID; ?>>回論壇</a></div><br><br>

<?php print $endFormat; ?>
