<?php

include __DIR__ . '/CrudeForum/bootstrap.php';
print $beginFormat;

$lock = $forum->getLock();
$postID = getenv ("QUERY_STRING");

try {
    $postSummary = $forum->readPostSummary($postID);
} catch (Exception $e) {
    die($e->getMessage());
}

?>

<META HTTP-EQUIV=Refresh CONTENT="0; URL=forum.php?<?php echo 100 * floor ($postSummary->pos / 100); ?>">
<link rel=stylesheet href=forum.css>

<?php print $endFormat; ?>
