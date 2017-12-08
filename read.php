<?php
	include __DIR__ . '/CrudeForum/bootstrap.php';
	$filename = getenv ("QUERY_STRING");
	forumLog ("read?" . $filename);
?>

<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel=stylesheet href=forum.css>

<?php
	print $beginFormat;

  // added by Koala
  $subdir = floor((int) $filename / 1000) . "/";
  // addition ended
	$text = fopen ($dataDirectory . $subdir . $filename, "r+");
	if($text) {
		$title = fgets ($text, 4096);
?>

<title><?php echo $title; ?></title>
<div class=text><?php echo $title; ?></div><br><br><br><div class=buttons>
<a href=sayForm.php?<?php echo $filename; ?>>回覆</a>&nbsp;
<a href=prev.php?<?php echo $filename; ?>>上一發言</a>&nbsp;
<a href=next.php?<?php echo $filename; ?>>下一發言</a>&nbsp;
<a href=back.php?<?php echo $filename; ?>>回論壇</a><br><br>
</div><hr><br><div class=text>

<?php
		$lineBreak = "<br>";
		while(!feof ($text)) {
			$line = fgets ($text, 4096);
			if(is_string (strstr ($line, "NO-LINE-END-BR")))
				$lineBreak = "";
			else
				print $line . $lineBreak;
		}
	} else print "Unable to open file";
?>

</div><br><hr><br><div class=buttons>
<a href=sayForm.php?<?php echo $filename; ?>>回覆</a>&nbsp;
<a href=prev.php?<?php echo $filename; ?>>上一發言</a>&nbsp;
<a href=next.php?<?php echo $filename; ?>>下一發言</a>&nbsp;
<a href=back.php?<?php echo $filename; ?>>回論壇</a></div><br><br>

<?php
	print $endFormat;
?>
