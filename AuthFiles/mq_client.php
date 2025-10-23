<?php
require_once "/opt/it490/path.inc";
require_once "/opt/it490/get_host_info.inc";
require_once "/opt/it490/rabbitMQLib.inc";

function mq_rpc(array $request) {
  $client = new rabbitMQClient("/opt/it490/testRabbitMQ.ini","testServer");
  $response = $client->send_request($request);
  if (!is_array($response)) {
    return ['status'=>'error','message'=>'Bad MQ'];
  }
  return $response;
}
?>
