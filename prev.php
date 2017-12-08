<?php
	include __DIR__ . '/CrudeForum/bootstrap.php';
	print $beginFormat;

	$lock = getLock ();
	$messageNo = getenv ("QUERY_STRING");
	$previousArticleNo = 0;

	$index = fopen ($dataDirectory . "index", "r+");
	if($index) {
		while(!feof ($index)) {
			$line = fgets ($index, 4096);
			$out = array ($line);
			list ($articleNo, $indent, $subject, $author, $time) = explode ("\t", $out[0]);
			if($articleNo == $messageNo) break;
			$previousArticleNo = $articleNo;
		}
		fclose ($index);
	}

	if($previousArticleNo == 0) $previousArticleNo = $messageNo;
	fclose ($lock);
?>

<META HTTP-EQUIV=Refresh CONTENT="0; URL=read.php?<?php echo $previousArticleNo; ?>">
<link rel=stylesheet href=forum.css>

<?php
	print $endFormat;
?>
