<?php
namespace app\common\service\wechat;

class Jssdk
{
    //获取access_token
    public static function getAccessToken()
    {
        $cache_name = 'wechat_access_token';
        $access_token = cache($cache_name);
        if(!$access_token){
            $result = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.Config::APPID.'&secret='.Config::APPSECRET);
            $result===false && abort(4000,'获取access_token异常');
            $result = json_decode($result,true);
            isset($result['errcode']) && abort($result['errcode'],$result['errmsg']);
            $access_token = $result['access_token'];
            cache($cache_name,$access_token,7000);
        }
        return $access_token;

    }

    //获取jsapi_ticket
    public static function getTicket($access_token='')
    {
        $cache_name = 'wechat_jsapi_ticket';
        $ticket = cache($cache_name);
        if(!$ticket){
            $access_token = $access_token ? $access_token : self::getAccessToken();
            $result = file_get_contents('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi');
            $result===false && abort(4000,'获取ticket异常');
            $result = json_decode($result,true);
            $result['errcode']!==0 && abort($result['errcode'],$result['errmsg']);
            $ticket = $result['ticket'];
        }
        return $ticket;
    }

    //签名算法
    public static function sign($url)
    {

        $data =[
            'noncestr'      => rand(10000,99999),
            'jsapi_ticket'  => self::getTicket(),
            'timestamp'     => time(),
            'url'           =>  $url
        ];

        ksort($data);
        $str = '';
        foreach ($data as $key=>$vo) {
            $str .= $key.'='.$vo.'&';
        }
        $str = substr($str,0,-1);
        $data['signature'] = sha1($str);
        $data['appId'] = Config::APPID;
        return $data;


    }
}