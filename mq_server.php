#!/usr/bin/php
<?php
require_once "/opt/it490/path.inc";
require_once "/opt/it490/get_host_info.inc";
require_once "/opt/it490/rabbitMQLib.inc";

$DB_HOST="127.0.0.1";
$DB_USER="testUser";
$DB_PASS="12345";
$DB_NAME="tune";

function db(){
  static $m; global $DB_HOST,$DB_USER,$DB_PASS,$DB_NAME;
  if(!$m){
    $m=new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
    if($m->connect_error){ throw new Exception("DB connect failed"); }
  }
  return $m;
}

function handle_register($u,$e,$p){
  $chk=db()->prepare("SELECT 1 FROM users WHERE username=? OR email=?");
  $chk->bind_param("ss",$u,$e); $chk->execute(); $chk->store_result();
  if($chk->num_rows>0) return ['status'=>'error','message'=>'Taken'];
  $hash=password_hash($p,PASSWORD_DEFAULT);
  $ins=db()->prepare("INSERT INTO users (username,email,password_hash,created_at) VALUES (?,?,?,NOW())");
  $ins->bind_param("sss",$u,$e,$hash);
  return $ins->execute()?['status'=>'ok']:['status'=>'error','message'=>'DB insert failed'];
}

function handle_login($u,$p){
  $q=db()->prepare("SELECT id,password_hash FROM users WHERE username=?");
  $q->bind_param("s",$u); $q->execute(); $q->bind_result($id,$hash);
  if(!$q->fetch() || !password_verify($p,$hash)) return ['status'=>'fail'];
  $sk=bin2hex(random_bytes(32));
  $ins=db()->prepare("INSERT INTO user_sessions (user_id,session_key,created_at) VALUES (?,?,NOW())");
  $ins->bind_param("is",$id,$sk); $ins->execute();
  return ['status'=>'ok','user_id'=>$id,'username'=>$u,'session_key'=>$sk];
}

//
function handle_logout($uid,$sk){
  $u=db()->prepare("UPDATE user_sessions SET ended_at=NOW() WHERE user_id=? AND session_key=? AND ended_at IS NULL");
  $u->bind_param("is",$uid,$sk); $u->execute();
  return ['status'=>'ok'];
}

function requestProcessor($r){
  switch(strtolower($r['type']??'')){
    case 'register': return handle_register($r['username'],$r['email'],$r['password']);
    case 'login':    return handle_login($r['username'],$r['password']);
    case 'logout':   return handle_logout((int)($r['user_id']??0), $r['session_key']??'');
    default: return ['status'=>'error','message'=>'Unknown request'];
  }
}

$server=new rabbitMQServer("/opt/it490/testRabbitMQ.ini","testServer");
echo " [*] Auth worker waiting on auth_queue...\n";
$server->process_requests('requestProcessor');
