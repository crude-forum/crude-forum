<?php
include __DIR__ . '/CrudeForum/bootstrap.php';
print $beginFormat;

$postID = getenv ("QUERY_STRING");
$post = $forum->readPost($postID);

?>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<head><title><?php echo $post->title; ?></title></head>
<link rel=stylesheet href=forum.css>
<body>
<center><form method=post name=form action=edit.php?<?php echo $postID; ?>>
<textarea name=text rows=40 cols=80
    style="font-size:13"><?php echo $post->safeBody(); ?></textarea>
<br><input type=submit value='.' autofocus></form>

</body>
