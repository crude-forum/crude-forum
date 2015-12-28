<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<script>
<!--
function setFocus () { document.form.text.focus (); }
document.getElementById("tcejbus").value = "123";
// -->
</script>
<link rel=stylesheet href=forum.css>
<body onload="setFocus ()">
<?php
 include 'configuration.php';
 print $beginFormat;
?>

<font face=courier><form method=post name=form action=say.php?<?= getenv ("QUERY_STRING");?>>
你的名字：
<input type=name name=em_an value="<?php if(isset ($_COOKIE["forumName"])) print $_COOKIE["forumName"]; ?>" size=40><br>

<?php
 $filename = getenv ("QUERY_STRING");
 // added by Koala
 $subdir = floor((int) $filename / 1000) . "/";
 // addition ended
 if(strlen ($filename)) $text = fopen ($dataDirectory . $subdir . $filename, "r+");
	else $text = 0;
 if($text) {
	$title = fgets ($text, 4096);
	if(strncmp ($title, "Re:", 3) != 0) $title = "Re:" . $title;
?>

主題　　：
<input type=name name=tcejbus value="<?= $title ?>" size=40><br>
<textarea name=tx_et rows=30 cols=80><?
	fgets ($text, 4096);
	while(!feof ($text)) {
	 $line = fgets ($text, 4096);
	 print "| " . $line;
	}
	print "\n\n\n</textarea>";
 } else {
?>

主題　　：
<input type=name name=tcejbus value='' size=40><br>
<textarea name=tx_et rows=30 cols=80></textarea>

<?
 }
?>

<br><input type=submit value='發出'></form>

<?
 print $endFormat;
?>

</font></body>
