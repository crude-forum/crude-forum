<?php

include __DIR__ . '/CrudeForum/bootstrap.php';
include 'rss.lang.big5.php';


// a little configuration //
$cfgThreadTotal=10;



$lock = getLock ();
$index = fopen ($dataDirectory . "index", "r+");
 
if($index) {
	$nSubjects = 0; // Limits number of subjects in a page
	$nThread = 0; // provide initial value to prevent warning
	$mode = isset($_GET['mode']) ? $_GET['mode'] : NULL;

	switch ($mode) {
	default:
	case 'post':
		while(!feof ($index) && $nThread<$cfgThreadTotal) {
			$line = fgets ($index, 4096);
			$out = array ($line);
			if(trim ($out[0]) != "") {
				list ($articleNo, $indent, $subject, $author, $time) = explode ("\t", $out[0]);
        $subdir = floor((int) $articleNo / 1000) . "/";
				$content = htmlspecialchars(
					'------------------<br/>[<a href="http://stupidsing.no-ip.org/sayForm.php?'.$articleNo.'">'.$lang['reply'].'</a>]&nbsp;&nbsp;'.
					'[<a href="http://stupidsing.no-ip.org/forum.php">'.$lang['forumIndex'].'</a>] <br/>------------------<br/>'.
					$lang['author'].': '.$author.'<br/><br/>'.
					implode('<br/>', array_slice(explode("\n", file_get_contents($dataDirectory . $subdir . $articleNo)), 5)).
					'<br/>------------------<br/>[<a href="http://stupidsing.no-ip.org/sayForm.php?'.$articleNo.'">'.$lang['reply'].'</a>]&nbsp;&nbsp;'.
					'[<a href="http://stupidsing.no-ip.org/forum.php">'.$lang['forumIndex'].'</a>] <br/>'.
					'==============================');

				
				if($articleNo == "") break; // Blank line, end of file
				if($indent == 0) $nThread++;
				
				// put post info into array
				$postArr[]=array(
	  'articleNo' => $articleNo,
					'subject' => $subject,
					'author' => $author,
					'time' => $time,
					'timestamp' => trim(preg_replace('/^(.+?) (.+?) (.+?) (\d\d)\:(\d\d):(\d\d) (\d\d\d\d)/', '$1, $3 $2 $7 $4:$5:$6 +0800', $time)),
					'content' => $content
				);
			}
		}
		break;
	case 'thread':
	case 'threads':
		$nThread=0;
		while(!feof ($index) && $nThread<$cfgThreadTotal ) {
			$line = fgets ($index, 4096);
			$out = array ($line);
			if(trim ($out[0]) != "") {
				list ($articleNo, $indent, $subject, $author, $time) = explode ("\t", $out[0]);
        $subdir = floor((int) $articleNo / 1000) . "/";
				$content = htmlspecialchars(
					'------------------<br/>[<a href="http://stupidsing.no-ip.org/sayForm.php?'.$articleNo.'">'.$lang['reply'].'</a>]&nbsp;&nbsp;'.
					'[<a href="http://stupidsing.no-ip.org/forum.php">'.$lang['forumIndex'].'</a>] <br/>------------------<br/>'.
					$lang['author'].': '.$author.'<br/><br/>'.
					implode('<br/>', array_slice(explode("\n", file_get_contents($dataDirectory . $subdir . $articleNo)), 5)).
					'<br/>------------------<br/>[<a href="http://stupidsing.no-ip.org/sayForm.php?'.$articleNo.'">'.$lang['reply'].'</a>]&nbsp;&nbsp;'.
					'[<a href="http://stupidsing.no-ip.org/forum.php">'.$lang['forumIndex'].'</a>] <br/>'.
					'=============================='
				);
				
	if($articleNo == "") break; // Blank line, end of file
				if($indent == 0) {
	  $nThread++;

					// put post info into array
					$postArr[]=array(
						'articleNo' => $articleNo,
						'subject' => $subject,
						'author' => $author,
						'time' => $time,
						'timestamp' => trim(preg_replace('/^(.+?) (.+?) (.+?) (\d\d)\:(\d\d):(\d\d) (\d\d\d\d)/', '$1, $3 $2 $7 $4:$5:$6 +0800', $time)),
						'content' => $content
					);
				}
			}
		}
		break;
	}
}

fclose ($index);
fclose ($lock);

/*
echo 'Tue, 03 Jun 2003 09:39:21 GMT<br/>';
echo $postArr[1]['time'] . '<br/>';
$test = preg_replace('/^(.+?) (.+?) (.+?) (\d\d)\:(\d\d):(\d\d) (\d\d\d\d)'."\n".'$/', '$1, $3 $2 $7 $4:$5:$6 +0800', $postArr[1]['time']);
echo $test.'<br/>';
echo "'".str_replace(" ", '&nbsp;', str_replace("\r", '\r', str_replace("\n", '\n', $test)))."'";
exit;
*/

header("Content-Type: text/xml; charset=utf-8");
echo '<?xml version="1.0"?>'."\n";
?>
<rss version="2.0">
	 <channel>
			<title>La sfolgorante idea...!!!</title>
			<link>http://stupidsing.no-ip.org/forum.php</link>
			<description></description>
			<language>zh-hk</language>
			<pubDate><?php print date('D, d M Y H:i:s ', time()); ?> GMT</pubDate>

			<lastBuildDate><?php print date('D, d M Y H:i:s ', time()); ?> GMT</lastBuildDate>
			<docs>http://blogs.law.harvard.edu/tech/rss</docs>
			<generator>Custom PHP by Koala Yeung</generator>
			<managingEditor>stupidsing@yahoo.com</managingEditor>
			<webMaster>stupidsing@yahoo.com</webMaster>
<?php foreach ((array) $postArr as $post): ?>
			<item>
				 <title><?php echo $post['subject']; ?></title>
				 <link>http://stupidsing.no-ip.org/read.php?<?php echo $post['articleNo']; ?></link>
				 <description><?php echo $post['content']; ?></description>
				 <pubDate><?php echo $post['timestamp']; ?></pubDate>
				 <guid>http://stupidsing.no-ip.org/read.php?<?php echo $post['articleNo']; ?></guid>
			</item>
<?php endforeach; ?>
	 </channel>
</rss>
