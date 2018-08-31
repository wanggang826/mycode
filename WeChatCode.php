<?php
/**
 * Created by PhpStorm.
 * User: yiming
 * Date: 18-7-31
 * Time: 上午9:26
 * 微信公众号推广支持二维码
 */
require_once "sCurl.php";
class WeChatCode {

    //获取微信access_token
    public function getAccessToken()
    {
        $app_id = env('weapp.app_id', 'wxcad5914060366ead');
        $app_secret = env('weapp.secret', '361ae0c57dae29e38a7d31ac82621572');
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$app_id."&secret=".$app_secret;
        $info=$this->httpCurl($url);
        return $info['access_token'];
    }

    //获取场景的临时二维码
    public function makeQrcodeWithSceneId($sceneId,$exptime = 604800){
        $fileName = "/qrcode/qrcode-sid-" . $sceneId . ".jpg";
        $filePath = DATA_DIR . $fileName;
        $codeData = $this->getQrcodeFromWeiXin($sceneId,$exptime);
        file_put_contents($filePath, $codeData);
        return ROOT . 'static' . $fileName;
    }

    //获取场景的永久二维码
    public function makeQrcodeWithStr($str){
        $fileName = "/qrcode/qrcode-sid-" . $str . ".jpg";
        $filePath = DATA_DIR . $fileName;
        $codeData = $this->getQrcodeFromWeiXinStr($str);
        file_put_contents($filePath, $codeData);
        return ROOT . 'static' . $fileName;
    }

    //向微信申请带场景ID二维码
    public function getQrcodeFromWeiXin($sceneId, $exptime) {

        $access_token = $this->getAccessToken();

        $ticket = $this->createQrcode($access_token, $sceneId, NULL, $exptime); //临时二维码(有效期 7天)
        if (!$ticket['ticket']) {
            return $ticket; // errcode
        }
        $imgData = $this->getQrcode($ticket['ticket']);
        
        return $imgData;
    }

    //向微信申请带场景字符串二维码
    public function getQrcodeFromWeiXinStr($str) {

        $access_token = $this->getAccessToken();

        $ticket = $this->createStrQrcode($access_token, $str);
        if (!$ticket['ticket']) {
            return $ticket; // errcode
        }
        $imgData = $this->getQrcode($ticket['ticket']);

        return $imgData;
    }

    public function createQrcode($wxAccessToken, $qid, $type='LIMIT', $exptime=604800) {
        $api="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$wxAccessToken;

        if($type=='LIMIT'){
            $data="{\"action_name\": \"QR_LIMIT_SCENE\", \"action_info\": {\"scene\": {\"scene_id\": $qid}}}";
        }else{
            $data="{\"expire_seconds\": $exptime,\"action_name\": \"QR_SCENE\", \"action_info\": {\"scene\": {\"scene_id\": $qid}}}";
        }

        $wxcurl=new sCurl($api,'POST',$data);

        return json_decode($wxcurl->sendRequest(),1);
    }


    public function createStrQrcode($wxAccessToken, $str) {
        $api="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$wxAccessToken;

        $data="{\"action_name\": \"QR_LIMIT_STR_SCENE\", \"action_info\": {\"scene\": {\"scene_str\": \"$str\"}}}";

        $wxcurl=new sCurl($api,'POST',$data);

        return json_decode($wxcurl->sendRequest(),1);
    }
    
    public function getQrcode($ticket) {
        $api="https://mp.weixin.qq.com/cgi-bin/showqrcode";

        $data=array(
            'ticket'=>$ticket
        );

        $wxcurl=new sCurl($api,'GET',$data);

        return $wxcurl->sendRequest();
    }
}