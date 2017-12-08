<?php

	include __DIR__ . '/CrudeForum/bootstrap.php';
	print $beginFormat;

	$lock = getLock ();
	$messageNo = getenv ("QUERY_STRING");
	$count = 0;

	$index = fopen ($dataDirectory . "index", "r+");
	if($index) {
		while(!feof ($index)) {
			$line = fgets ($index, 4096);
			$out = array ($line);
			list ($articleNo, $indent, $subject, $author, $time) = explode ("\t", $out[0]);
			if($articleNo == $messageNo) break;
			$count++;
		}
		fclose ($index);
	}

	fclose ($lock);
?>

<META HTTP-EQUIV=Refresh CONTENT="0; URL=forum.php?<?php echo 100 * floor ($count / 100); ?>">
<link rel=stylesheet href=forum.css>

<?php
	print $endFormat;
?>
