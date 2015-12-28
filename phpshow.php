<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<script>
<!--
function setFocus () { document.form.text.focus (); }
// -->
</script>
<link rel=stylesheet href=hidden.css>
<body onload="setFocus ()">

<?php
	include 'configuration.php';
	print $beginFormat;

	if($_COOKIE["forumName"] != $administrator) {
		print "You are unauthorized.";
		exit;
	}

	$messageNo = getenv ("QUERY_STRING");
	$messageNo = str_replace ("/", "", $messageNo);
?>

<center><form method=post name=form action=phpedit.php?<?= $messageNo; ?>>
<textarea name=text rows=32 cols=120><?php
 $text = fopen ($messageNo, "r+");
	if($text)
		while(!feof ($text)) {
			$line = fgets ($text, 4096);
			$line = str_replace ("&", "&amp;", $line); /* Jumps the & sign! */
			$line = str_replace ("<", "&lt;", $line); /* Jumps all HTML tags */
			print $line;
		}
	fclose ($text);
?></textarea><br><input type=submit value='.'></form>

</font></body>
