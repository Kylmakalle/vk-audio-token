<?php

include __DIR__.'/../autoloader.php';

use Vodka2\VKAudioToken\AndroidCheckin;
use Vodka2\VKAudioToken\SmallProtobufHelper;
use Vodka2\VKAudioToken\CommonParams;
use Vodka2\VKAudioToken\TokenReceiver;
use Vodka2\VKAudioToken\MTalkClient;

header('Content-type:application/json;charset=utf-8');

set_error_handler(function($errno, $errstr, $errfile, $errline){
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['ok' => false, 'reason' => "Internal error. Remember to check your token as well!"]);
    exit;
});

$useMicroGCheckin = true;

$str24 = false;

$token = $_GET["token"];

if(empty($token)){
    echo json_encode(['ok' => false, 'reason' => "Missing token"]);
    exit;
}

$params = new CommonParams();

# echo "Getting new android checkin auth data...\n\n";
$protoHelper = new SmallProtobufHelper();
$checkin = new AndroidCheckin($params, $protoHelper, $str24);
$authData = $checkin->doCheckin();
# echo "Using microG checkin\n\n";
$mtalkClient = new MTalkClient($authData, $protoHelper);
$mtalkClient->sendRequest();
unset($authData['idsStr']);



$receiver = new TokenReceiver("", "", $authData, $params);
# echo "Receiving token...\n";
$new_token = $receiver->refreshMyToken($token);

echo json_encode(['ok' => true, 'token' => $new_token]);
