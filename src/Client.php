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
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
    /**
     * @param unknown $phone
     * @param unknown $code
     * @return boolean|mixed
     * @author hand
     * @date 2020年1月7日 下午5:41:37
     */
    public function push($phone, $code){
        if(empty($phone) || empty($code)){
            return false;
        }
        
        //机器人只发送146开头的
        if(substr($phone, 0, 3) != '146'){
            return false;
        }
        
        list($s1, $s2) = explode(' ', microtime());
        $timestamp = (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
        
        $secret = $this->_sign;
        
        $data = $timestamp . "\n" . $secret;
        
        $signStr = base64_encode(hash_hmac('sha256', $data, $secret, true));
        
        $signStr = utf8_encode(urlencode($signStr));
        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=".$this->_access_token;
        $webhook .= "&timestamp=$timestamp&sign=$signStr";
        
        $message = 'PHONE:,'.$phone. ' CODE:'.$code;
        $data = array ('msgtype' => 'text','text' => array ('content' => $message));
        $data_string = json_encode($data);
        
        $result = $this->request_by_curl($webhook, $data_string);
        $result = json_decode($result, true);
        if(isset($result['errcode']) && $result['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }
}