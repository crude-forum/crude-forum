<?php

include __DIR__ . '/CrudeForum/bootstrap.php';

use ywsing\CrudeForum\Post;

$postID = (int) getenv("QUERY_STRING");

$post = ($postID > 0) ? $forum->readPost($postID) : NULL;
if ($post === NULL) $post = new Post('', '');

?>

<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<script>
<!--
function setFocus () { document.form.text.focus (); }
document.getElementById("tcejbus").value = "123";
// -->
</script>
<link rel=stylesheet href=forum.css>
<body onload="setFocus ()">
<?php print $beginFormat; ?>

<font face=courier><form method=post name=form action=say.php?<?php echo getenv ("QUERY_STRING");?>>
你的名字：
<input type=name name=em_an value="<?php if(isset ($_COOKIE["forumName"])) print $_COOKIE["forumName"]; ?>" size=40><br>

主題　　：
<input
  type=name name=tcejbus
  value="<?php echo htmlspecialchars($post->title) ?>" size=40><br>
<textarea name=tx_et rows=30 cols=80><?php echo $post->saveBody(); ?></textarea>

<br><input type=submit value='發出'></form>

<?php print $endFormat; ?>

</font></body>
