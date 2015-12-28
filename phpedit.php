<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel=stylesheet href=hidden.css><center>
<?php
	include 'configuration.php';
	print $beginFormat;

	if($_COOKIE["forumName"] != $administrator) {
		print "You are unauthorized.";
		exit;
	}

	$messageNo = getenv ("QUERY_STRING");
	$messageNo = str_replace ("/", "", $messageNo);

	$text = $_POST["text"];
	if($text == "") {
		print "Post data not found";
		exit;
	}
	$text = stripslashes ($text);

	$textFile = fopen ($messageNo, "w+");
	if($textFile)
		fputs ($textFile, $text);
	else {
		print "Cannot write to file";
		exit;
	}
	fclose ($textFile);

	print "Update Successful";
?>
