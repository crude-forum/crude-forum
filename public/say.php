<?php

include __DIR__ . '/../bootstrap.php';
use ywsing\CrudeForum\Post;
use ywsing\CrudeForum\PostSummary;

setlocale (LC_TIME, "C");

$parentID = $_SERVER['QUERY_STRING'] ?? '';
$forum->log ("say?" . $parentID);

if (empty(trim($parentID))) $parentID = FALSE;

$author = stripslashes ($_POST["em_an"]);
$title = stripslashes ($_POST["tcejbus"]);
$body = stripslashes ($_POST["tx_et"]);
$currentTime = strftime ("%a %F %T");

// Characters to be avoided
$author = str_replace(["\n", "\r", "\t"], ' ', $author);
$title = str_replace (["\n", "\r", "\t"], ' ', $title);
$title = str_replace ("\022", "'", $title);
$body = "來自：" . $author . "\n時間：" . $currentTime . "\n\n" . $body;
$body = str_replace ("<", "&lt;", $body);
$body = str_replace (">", "&gt;", $body);
$body = str_replace ("\r\n", "\n", $body); // use UNIX linebreak
if ($author <> '')
    setcookie ('forumName', $author, mktime (0, 0, 0, 1, 1, 2038), "/");

if (strlen($author) > 8) die('Name too long');
if ($author == '') die('Please Enter your name');
if ($title == '') die('Please Enter a subject');

print $beginFormat;
$lock = $forum->getLock();

// Gets messages count for assigning post number
$nextID = $forum->getCount() + 1;
$forum->writePost($nextID, new Post(
    $title,
    $body
));
$forum->appendIndex(new PostSummary(
    $nextID,
    0,
    $title,
    $author,
    $currentTime
), $parentID);
$forum->incCount();

fclose($lock);

?>

<meta http-equiv='Content-Type' content='text/html; charset=big5'>
<link rel=stylesheet href=forum.css>
<META HTTP-EQUIV=Refresh CONTENT="0; URL=forum.php">

<?php print $endFormat; ?>
