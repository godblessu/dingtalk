<?php
/**
 * client.php
 * 钉钉加签方式发送验证码
 * @Copyright(c) 2020 meicai
 * @author hand
 * @date: 2020年1月3日 上午11:45:54
 */

namespace spruce\dingtalk;

class Client
{
    private $_access_token = '';
    
    private $_sign = '';
    
    public function __construct($token,$sign){
        $this->_access_token = $token;
        $this->_sign = $sign;
    }
    
    public function request_by_curl($remote_server, $post_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        
        return $data;
    }  
    
    public function push($message){
        if(empty($message)){
            return false;
        }
        list($s1, $s2) = explode(' ', microtime());
        $timestamp = (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
        
        $secret = $this->_sign;
        
        $data = $timestamp . "\n" . $secret;
        
        $signStr = base64_encode(hash_hmac('sha256', $data, $secret));
        
        $signStr = utf8_encode(urlencode($signStr));
        
        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=".$this->_access_token;
        
        $webhook .= "&timestamp=$timestamp&sign=$signStr";
        
        $data = array ('msgtype' => 'text','text' => array ('content' => $message));
        $data_string = json_encode($data);
        
        $result = request_by_curl($webhook, $data_string);
        return $result;
    }
}