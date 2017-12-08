<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<?php
	include 'configuration.php';
	print $beginFormat;

	$messageNo = getenv ("QUERY_STRING");
	$messageNo = str_replace ("/", "", $messageNo);
?>

<head><title><?php echo $messageNo; ?></title></head>
<link rel=stylesheet href=forum.css>
<body onload="document.form.text.focus ();">

<center><form method=post name=form action=edit.php?<?php echo $messageNo; ?>>
<textarea name=text rows=40 cols=80 style="font-size:13">
<?php
    if($messageNo != 'notes') $subdir = floor((int) $messageNo / 1000) . "/";
    else $subdir = '';
    $filename = $dataDirectory . $subdir . $messageNo;
	$text = fopen ($filename, "r+");
	if($text)
		while(!feof ($text)) {
			$line = fgets ($text, 4096);
			$line = str_replace ("&", "&amp;", $line); /* Jumps the & sign! */
			$line = str_replace ("<", "&lt;", $line); /* Jumps all HTML tags */
			print $line;
		}
	fclose ($text);
?>
</textarea><br><input type=submit value='.'></form>

</body>
