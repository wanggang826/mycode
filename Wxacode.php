<?php
/**
 * Created by PhpStorm.
 * User: yiming
 * Date: 18-8-31
 * Time: 上午9:11
 * 小程序场景参数二维码
 */
class Wxacode{

    //获取微信access_token
    public function getAccessToken()
    {
        $app_id = env('weapp.app_id', 'wxcad5914060366ead');
        $app_secret = env('weapp.secret', '361ae0c57dae29e38a7d31ac82621572');
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$app_id."&secret=".$app_secret;
        $info=$this->httpCurl($url);
        return $info['access_token'];
    }


    public function httpCurlPost($url, $post_data = '', $timeout = 5)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($post_data != '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return $file_contents;

    }

    public static function httpCurl($url) {
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $output=curl_exec($ch);
        curl_close($ch);
        $info=json_decode($output,true);
        return $info;
    }

    /**
     * 进入小程序二维码
     * 参数:场景参数,进入页面
     */
    public function getWxaCode(){
        header('content-type:image/jpg');
        $scene = input('scene');$page = input('page');
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token;
        $post_data = [
            'scene'=>$scene,
            'page' =>$page,
        ];
        $codeImg =  $this->httpPost($url,json_encode($post_data));
        $codeName = '/wxa_'.time().'jpg';
        $path = 'upload/wxacode/'.date('Y').'/'.date('m').'/'.date('d');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $file = $path.$codeName;
        file_put_contents($file, $codeImg);
        $data = ['status'=>0,'msg'=>'操作成功','data'=>$file];
        echo json_encode($data);
    }
}