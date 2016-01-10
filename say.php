<?php
	include 'configuration.php';
	include 'misc.php';
	setlocale (LC_TIME, "C");

	$parent = getenv ("QUERY_STRING");
	forumLog ("say?" . $parent);

	$author = stripslashes ($_POST["em_an"]);
	$subject = stripslashes ($_POST["tcejbus"]);
	$text = stripslashes ($_POST["tx_et"]);
	$currentTime = strftime ("%a %F %T");

	 // Characters to be avoided
	$author = str_replace ("\n", " ", $author);
	$author = str_replace ("\r", " ", $author);
	$author = str_replace ("\t", " ", $author);
	$subject = str_replace ("\n", " ", $subject);
	$subject = str_replace ("\r", " ", $subject);
	$subject = str_replace ("\t", " ", $subject);
	$subject = str_replace ("\022", "'", $subject);
	$text = "來自：" . $author . "\n時間：" . $currentTime . "\n\n" . $text;
	$text = str_replace ("<", "&lt;", $text);
	$text = str_replace (">", "&gt;", $text);
	if($author <> "")
		setcookie ("forumName", $author, mktime (0, 0, 0, 1, 1, 2038), "/");
?>

<meta http-equiv='Content-Type' content='text/html; charset=big5'>
<link rel=stylesheet href=forum.css>

<?php
	if(strlen ($author) > 8) {
		print "Name too long";
		exit;
	}
	if($author == "") {
		print "Please Enter your name";
		exit;
	}
	if($subject == "") {
		print "Please Enter a subject";
		exit;
	}

	print $beginFormat;
	$lock = getLock ();

	 // Gets messages count for assigning post number
	$countFile = fopen ($dataDirectory . "count", "r+");
	if($countFile) {
		fscanf ($countFile, "%d", $count);
		fclose ($countFile);
	} else {
		printf ("Cannot open count file for reading");
		exit;
	}
	$count++;
	$countFile = fopen ($dataDirectory . "count", "w+");
	if($countFile) {
		fputs ($countFile, $count);
		fclose ($countFile);
	} else {
		print "Cannot open count file for writing";
		exit;
	}

	 // Writes post file
  // added by Koala
  $subdir = floor((int) $count / 1000) . "/";
  if (!is_dir($dataDirectory . $subdir)) {
    mkdir($dataDirectory . $subdir);
    chmod($dataDirectory . $subdir, 0777);
  }
  // addition ended
	$textFile = fopen ($dataDirectory . $subdir . $count, "w+");
	fputs ($textFile, sprintf ("%s\n\n%s", $subject, $text));
	fclose ($textFile);

	 // Updates index file
	rename ($dataDirectory . "index", $dataDirectory . "index.old");
	$index = fopen ($dataDirectory . "index.old", "r+");

	if($index) {
		$output = fopen ($dataDirectory . "index", "w+");

		if($output) {
			if($parent == "")
				fputs ($output, sprintf ("%d\t%d\t%s\t%s\t%s\n",
					$count, 0, $subject, $author, $currentTime));

			while(!feof ($index)) {
				$line = fgets ($index, 4096);
				$out = array ($line);
				if(trim ($out[0]) != "") {
					list ($articleNo, $indent, $s, $a, $t) = split ("\t", $out[0]);
					if (trim ($articleNo) != "" && trim ($s) != "" && trim($a) != "" && trim($t) != "") // filter spams
						fputs ($output, $line);

					if($parent != "" && $articleNo == $parent) { // Inserts at replying position
					 fputs ($output, sprintf ("%d\t%d\t%s\t%s\t%s\n",
						$count, $indent + 1, $subject, $author, $currentTime));
					}
				}
			}

			fclose ($output);
			fclose ($index);

			unlink ($dataDirectory . "index.old");
		} else {
			print "Unable to create new index file";
			rename ($dataDirectory . "index.old", $dataDirectory . "index");
		}
	} else print "Unable to open old index file";

	fclose ($lock);
?>

<META HTTP-EQUIV=Refresh CONTENT="0; URL=forum.php">

<?
 print $endFormat;
?>
