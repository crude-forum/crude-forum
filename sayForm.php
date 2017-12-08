<?php

include __DIR__ . '/CrudeForum/bootstrap.php';

use ywsing\CrudeForum\Post;

$postID = (int) getenv("QUERY_STRING");

$parent = ($postID > 0) ? $forum->readPost($postID) : NULL;
$post = Post::replyFor($parent);

?>

<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel=stylesheet href=forum.css>
<body onload="setFocus ()">
<?php print $beginFormat; ?>

<font face=courier><form method=post name=form action=say.php?<?php echo getenv ("QUERY_STRING");?>>
<label for="em_an">你的名字：</label>
<input id=em_an type=name name=em_an value="<?php if(isset ($_COOKIE["forumName"])) print $_COOKIE["forumName"]; ?>" size=40><br>

<label for="tcejbus">主題　　：</label>
<input
  id=tcejbus
  type=name name=tcejbus
  value="<?php echo $post->safeTitle(); ?>"
  size=40><br>
<textarea
  name=tx_et
  rows=30
  cols=80><?php echo $post->safeBody(); ?></textarea>

<br><input type=submit value='發出'></form>

<?php print $endFormat; ?>

</font></body>
