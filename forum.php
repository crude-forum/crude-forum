<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'configuration.php';
include 'misc.php';

$page = getenv ("QUERY_STRING");
forumLog ("forum?" . $page);
?>

<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel="alternate" type="application/rss+xml" title="Forum Threads - RSS" href="https://stupidsing.no-ip.org/rss.php?mode=thread" />
<link rel="alternate" type="application/rss+xml" title="Forum Posts - RSS" href="https://stupidsing.no-ip.org/rss.php?mode=post" />
<link rel=stylesheet href=forum.css>

<?php print $beginFormat; ?>

<title>Wandering and wonderings</title>
<table width=100%><tr><td><div class=buttons><a href=sayForm.php>發言</a>&nbsp;&nbsp;<a href=
forum.php?<?php if($page > 100) print $page - 100; else print '0'; ?>
>上一頁</a>&nbsp;&nbsp;<a href=forum.php>首頁</a>&nbsp;&nbsp;<a href=index.html>主頁</a></div></td>
<td align=right><div class=text>心意就如密友，長路裏相伴漫遊。</div></td></tr></table><br>

<hr><br><br><div class=miscs>

<?php
	$lock = getLock ();

	$indexfn = $dataDirectory . "index";
	if(!file_exists ($indexfn) && !touch ($indexfn)) {
		throw new Exception("unable to create index file: {$indexfn}");
	}
	$index = fopen ($indexfn, "r+");
	if($index) {
		$nSubjects = 0; // Limits number of subjects in a page

		$skipLines = $page;
		if($skipLines != "")
			while($skipLines--) fgets ($index, 4096);

		while(!feof ($index) && $nSubjects++ < 100) {
			$line = fgets ($index, 4096);
			$out = array ($line);
			if(trim ($out[0]) != "") {
				list ($articleNo, $indent, $subject, $author, $time) = explode ("\t", $out[0]);
				if($articleNo == "") break; // Blank line, end of file

				if($indent == 0 && $nSubjects > 1)
					print "<p></p>";
				for($i = 0; $i < $indent; $i++) print "　 ";
				print "<a href=read.php?" . $articleNo . ">" . $subject . "</a>";
				if($indent = 0)
					print "　　　 -- ";
				else print "　　-- ";
					print $author . " at " . $time . "<br>";
			}
		}
	}
	else print "Unable to open index file";

	fclose ($index);
	fclose ($lock);
?>

</div><br><br><hr><br>
<table width=100%><tr align=top><td><div class=buttons><a href=sayForm.php>發言</a>&nbsp;&nbsp;<a href=
forum.php?<?php print ((int) $page) + 100; ?>
>下一頁</a>&nbsp;&nbsp;<a href=forum.php>首頁</a>&nbsp;&nbsp;<a href=../index.html>回主頁</a></div></td><td align=right>漫長漫長夜間，我伴我閒談；漫長漫長夜晚，從未覺是冷。</td></tr></table>

<?php
	print $endFormat;
?>
