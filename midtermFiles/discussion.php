<?php

require_once "mq_client.php";
session_start();
$discussion_id = 1;
if (isset($_GET['id'])) {
    $discussion_id = $_GET['id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['content'])) {
    $request = array();
     $request['type'] = 'postMessage';
    $request['discussion_id'] = $discussion_id;
    $request['author_id'] = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $request['content'] = $_POST['content'];
    $response = mq_rpc($request);
    
    header("Location: discussion.php?id=" . $discussion_id); exit();
}
// we want to send a post request to our DB worker when our users submits our messages to get discussion id from db
$messageRequest = array();
$messageRequest['type'] = 'getMessages';
$messageRequest['discussion_id'] = $discussion_id;
$response = mq_rpc($messageRequest);

$messages = array();
if (isset($response['rows'])) {
    $messages = $response['rows'];
}?>
<html>
<head><title>Discussions</title></head>
<body>
<h2>Discussion #<?php echo $discussion_id; ?></h2>
<form method="POST">
    <textarea name="content" rows="5" cols="50"></textarea><br>
    <input type="submit" value="Post">
</form>
<hr>
    
<?php
if (!empty($messages)) {
    foreach ($messages as $row) {
        echo "<div>";
        echo "<b>User " . $row['author_id'] . ":</b><br>";
        echo $row['content'] . "<br>";
        echo "<small>" . $row['created_at'] . "</small>";
        echo "</div><br>"; } //simple view for our message history basic but fine for now
} else {
    echo "No messages yet."; } ?>
</body>
</html>
