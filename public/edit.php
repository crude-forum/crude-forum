<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel=stylesheet href=hidden.css><center>
<?php
    include __DIR__ . '/../bootstrap.php';

    use ywsing\CrudeForum\Post;

	print $beginFormat;

    $postID = $_SERVER['QUERY_STRING'] ?? '';
	$postID = str_replace (".", "", $postID);
    if (empty(trim($postID))) die('empty postID');

	$text = $_POST["text"];
	if($text == "") {
		print "Post data not found";
		exit;
	}
	$text = stripslashes ($text);

	 // Original author or authorized user?
	$user = $_COOKIE["forumName"] ?? '';
	if ($user != CRUDE_ADMIN) {
        $post = $forum->readPost($postID);
		if($text) {
            $lines = explode("\n", $post->body);
            $line = trim($lines[0]);
			if($line != "來自：" . $user) {
				print "You are not the original author.";
				exit;
			}
		}
	}

    try {
        $forum->writePost($postID, Post::fromText($text));
    } catch (\Exception $e) {
        die($e->getMessage());
    }
	print "Update Successful";
?>
