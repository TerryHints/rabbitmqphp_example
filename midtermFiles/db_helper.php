#!/usr/bin/php
<?php
require_once "/opt/it490/path.inc";
require_once "/opt/it490/get_host_info.inc";
require_once "/opt/it490/rabbitMQLib.inc";

$DB_HOST = "127.0.0.1";
$DB_USER = "testUser";
$DB_PASS = "12345";
$DB_NAME = "tune";

function db(){
    static $conn;
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    if(!$conn){
        $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        if($conn->connect_error){
            throw new Exception("DB connect failed: ".$conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
  }

function handle_register($username, $email, $password){ // registration that we get from register.php 
    $username = trim($username);
     $email = trim($email);
    $password = (string)$password;

    if($username === '' || $email === '' || $password === '')
    {
        return ['status'=>'error','message'=>'Missing fields'];
    }

    $check = db()->prepare("SELECT 1 FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();
    if($check->num_rows > 0){
        $check->close();
        return ['status'=>'error','message'=>'Username or email already exists'];
    }
    $check->close();

    $passHash = password_hash($password, PASSWORD_DEFAULT); //building it in with passHash so we'll have to compare later
    $query = db()->prepare("INSERT INTO users (username,email,password_hash,created_at) VALUES (?,?,?,NOW())");
    $query->bind_param("sss", $username, $email, $passHash);
    $ok = $query->execute();
    $query->close();

    return $ok ? ['status'=>'ok'] : ['status'=>'error','message'=>'DB insert failed'];
}

function handle_login($username, $password){
    $query = db()->prepare("SELECT id,password_hash FROM users WHERE username=?");
    $query->bind_param("s", $username);
    $query->execute();
    $query->bind_result($id, $passHash);

    if(!$query->fetch()){
        $query->close();
        return ['status'=>'fail'];
    }
    $query->close();

    if(!password_verify($password, $passHash)){
        return ['status'=>'fail'];
    }

    $session_key = bin2hex(random_bytes(32));
    $insert = db()->prepare("INSERT INTO user_sessions (user_id,session_key,created_at) VALUES (?,?,NOW())");
    $insert->bind_param("is", $id, $session_key);
    $insert->execute();
    $insert->close();

    return ['status'=>'ok','user_id'=>$id,'username'=>$username,'session_key'=>$session_key];
}

function handle_logout($user_id, $session_key){
    $update = db()->prepare("UPDATE user_sessions 
                            SET ended_at=NOW() WHERE user_id=? 
                            AND session_key=? AND ended_at IS NULL");
    $update->bind_param("is", $user_id, $session_key);
    $update->execute();
    $update->close();
    return ['status'=>'ok'];
}
//functions for discussion php
function handle_getMessages($discussion_id){
    $rows = array();
    $query = db()->prepare("SELECT * FROM discussion_messages WHERE discussion_id=? ORDER BY created_at DESC");
    $query->bind_param("i",$discussion_id);
    $query->execute();
    $result = $query->get_result(); while($row = $result->fetch_assoc()){
        $rows[] = $row;}
    $query->close();
    return array('status'=>'ok','rows'=>$rows);
}

//Add checks for discussion ID from the table
function handle_postMessage($discussion_id, $author_id, $content)
   {
  if (trim($content) === '') {
    return ['status'=>'error','message'=>'Empty message'];
  }

  //New checks
  $check = db()->prepare("SELECT id FROM discussions WHERE id=?");
  $check->bind_param("i", $discussion_id);
  $check->execute();
  $res = $check->get_result();

  if ($res->num_rows == 0) {
    $title = "Discussion #" . $discussion_id;
    $insert = db()->prepare("INSERT INTO discussions (title, created_by) VALUES (?, ?)");
    $insert->bind_param("si", $title, $author_id);
    $insert->execute();
    $insert->close();
  }

  $check->close();

  $query = db()->prepare("INSERT INTO discussion_messages (discussion_id,author_id,content,created_at)
                          VALUES (?,?,?,NOW())");
  $query->bind_param("iis", $discussion_id, $author_id, $content);
  $ok = $query->execute();
  $query->close();

  return ['status' => $ok ? 'ok' : 'error'];
}

function getOrCreateDiscussion($topic_type, $topic_ref, $creator_id = 1){
    $conn = db();
    $q = $conn->prepare("SELECT id FROM discussions WHERE topic_type=? AND topic_ref=? LIMIT 1");
    $q->bind_param("ss", $topic_type, $topic_ref);
    $q->execute();
    $q->bind_result($existing_id);
    if($q->fetch()){
        $q->close();
        return $existing_id;
    }
    $q->close();

    $ins = $conn->prepare("INSERT INTO discussions (creator_id,title,topic_type,topic_ref,created_at)
                           VALUES (?,?,?, ?, NOW())");
    $title = ucfirst($topic_ref) . " Discussion";
    $ins->bind_param("isss", $creator_id, $title, $topic_type, $topic_ref);
    $ins->execute();
    $new_id = $ins->insert_id;
    $ins->close();
    return $new_id;
}

function requestProcessor($request){
    $type = strtolower($request['type'] ?? '');
    switch($type){
        case 'register':
            return handle_register($request['username'] ?? '', $request['email'] ?? '', $request['password'] ?? '');
        case 'login':
            return handle_login($request['username'] ?? '', $request['password'] ?? '');
        case 'logout':
            return handle_logout((int)($request['user_id'] ?? 0), $request['session_key'] ?? '');
        //discussion.php fcases 
        case 'getmessages': 
            return handle_getMessages((int)($request['discussion_id'] ?? 1));
        case 'postmessage':
            return handle_postMessage(
                (int)($request['discussion_id'] ?? 1),
                (int)($request['author_id'] ?? 1), $request['content'] ?? '');
        
        default:
            return ['status'=>'error','message'=>'Unknown request type'];
    }
}

$server = new rabbitMQServer("/opt/it490/testRabbitMQ.ini","testServer");
echo " [*] Auth worker waiting on auth_queue...<n";
$server->process_requests('requestProcessor');
?>
