<?php
/**
 * TelegramBot
 * https://github.com/Saepulawr/telegrambot-php.git
 * 
 * **PLEASE ACTIVATE CURL EXTENSION ON YOUR PHP.INI**
 * 
 * use this file to handle incoming chat from your telegram bot.
 * function getUpdates() -> get updates chat from your telegram bot.
 * function sendMessage() -> send telegram messages to the intended person using chatID
 * 
 * MIT License

 * Copyright (c) 2020 Saepul Anwar
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
Class TelegramBot{
    private static $botId;
    private static $botKey;
    private static $error;
    private static $errorNo;
    private static $httpStatusCode;

    public function __construct($botId, $botKey)
    {
        self::$botId = $botId;
        self::$botKey = $botKey;
        
        
    }
    private static function initcurl($curl,$url,$method){
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    }
    private static function handleErrors($curl)
    {
        self::$error = curl_error($curl);
        self::$errorNo = curl_errno($curl);
        self::$httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (0 !== self::$errorNo) {
            throw new \RuntimeException(self::$error, self::$errorNo);
        }
    }
    private static function get($url)
    {
        $curl = curl_init();
        self::initcurl($curl,$url,"GET");
        $response = curl_exec($curl);
        self::handleErrors($curl);
        curl_close($curl);
        return $response;
    }
    private static function urlApi(){
        return "https://api.telegram.org/".self::$botId.":".self::$botKey;
    }
    private static function collectData($offset ="",$limit=""){
        return json_decode(self::get(self::urlApi()."/getUpdates?offset=$offset&limit=$limit"));
    }
    public function sendMessage($chatID,$message){
        $message = urlencode($message);
        self::get(self::urlApi()."/sendmessage?chat_id=$chatID&text=$message");
    }
    public function getUpdates(){
        $tmp = file_get_contents(".tmpTelegramBot");
        $dataResult = array();
        if(empty($tmp)){
            $tmp=array();
        }else{
            $tmp = json_decode($tmp);
            if(empty($tmp)) $tmp=array();
        }
        $lastUpdateID = array("plase don't edit or remove this data");
        $res = self::collectData();
        if($res->ok){
            foreach($res->result as $data){
                $update_id =$data->update_id;
                array_push($lastUpdateID,$update_id);
                if(empty(array_search($update_id,$tmp))){
                    array_push($dataResult,$data);
                }
            }
            file_put_contents(".tmpTelegramBot",json_encode($lastUpdateID));
            return json_encode(array(
                "ok" => "true",
                "result" => $dataResult
            ));
        }else{
            return json_encode(array(
                "ok" => "false",
                "result" => array()
            ));
        }
    }
}
?>
