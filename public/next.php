<?php
include __DIR__ . '/../bootstrap.php';
print $beginFormat;

$lock = $forum->getLock();
$postID = getenv ("QUERY_STRING");
try {
    $postSummary = $forum->readNextPostSummary($postID);
} catch (Exception $e) {
    die($e->getMessage());
}

?>

<META HTTP-EQUIV=Refresh CONTENT="0; URL=read.php?<?php echo $postSummary->id; ?>">
<link rel=stylesheet href=forum.css>

<?php print $endFormat; ?>
