<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel=stylesheet href=hidden.css><center>
<?php
	include __DIR__ . '/CrudeForum/bootstrap.php';
	print $beginFormat;

	$messageNo = getenv ("QUERY_STRING");
	$messageNo = str_replace (".", "", $messageNo);

	$text = $_POST["text"];
	if($text == "") {
		print "Post data not found";
		exit;
	}
	$text = stripslashes ($text);

	 // Original author or authorized user?
	$user = $_COOKIE["forumName"];
	if($user != $administrator) {
		$text = fopen ($dataDirectory . $messageNo, "r+");
		if($text) {
			fgets ($text, 4096);
			fgets ($text, 4096);
			$line = rtrim (fgets ($text, 4096));
			if($line != "來自：" . $user) {
				print "You are not the original author.";
				exit;
			}
		}
		fclose ($text);
	}

    if($messageNo != 'notes') $subdir = floor((int) $messageNo / 1000) . "/";
    else $subdir = '';
    $filename = $dataDirectory . $subdir . $messageNo;
	$textFile = fopen ($filename, "w+");
	if($textFile)
		fputs ($textFile, $text);
	else {
		print "Cannot write to file";
		exit;
	}
	fclose ($textFile);

	print "Update Successful";
?>
