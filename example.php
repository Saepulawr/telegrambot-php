<?php
include "telegrambot.php";
$config = array(
    "telegram_bot_id" =>  "YOUR BOT ID",
    "telegram_bot_key" => "YOUR BOT KEY",

);
$telegram = new TelegramBot($config['telegram_bot_id'],$config['telegram_bot_key']);
function main(){
    global $telegram;
    $res = json_decode($telegram->getUpdates());
    if(count($res->result)>0){
        foreach($res->result as $data){
            $telegram->sendMessage($data->message->chat->id,"test response : " . $data->message->text);
        }
    }
}

main();
?>