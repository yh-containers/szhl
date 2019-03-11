<?php
namespace app\common\service\wechat;

use http\Url;

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
    /*
     * 获取网页access_token
     * */
    public static function getWebAccessToken($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.Config::APPID.'&secret='.Config::APPSECRET.'&code='.$code.'&grant_type=authorization_code';
        $result = file_get_contents($url);
        if(isset($result['errcode'])){
            return false;
        }else{
            return json_decode($result,true);
        }
    }

    /*
     * 菜单栏
     * */
    public static function menu($button = [])
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.self::getAccessToken();
        $data = ['button'=>$button];
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        $result = net_req($url,$data,'POST',[
            'Content-Type: application/x-www-form-urlencoded'
        ],true);
        $result = json_decode($result,true);

        if($result && $result['errcode']>0){
            return false;
        }else{
            return true;
        }

    }

    /*
     * 带参二维码--user
     * */
    public static function qrcodeUser($user_id,$expire_seconds=604800)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.self::getAccessToken();
        //{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
        $data = [
            "expire_seconds" => $expire_seconds,
            "action_name"   => "QR_SCENE",
            "action_info"   => ["scene"=>[
                "scene_id"=>$user_id
            ]],
        ];
        $json = json_encode($data);
        $result = net_req($url,$json,'POST',[
            'Content-Type: application/x-www-form-urlencoded'
        ],true);
        $result = json_decode($result,true);
        if(isset($result['ticket'])){
            return [$result['ticket'],$result['expire_seconds'],$result['url']];
        }else{
            return false;
        }
    }

    /*
     * 获取素材
     * */
    public static function getMaterial($type = 'news',$offset=0,$count = 10)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.self::getAccessToken();
        $data = [
            "type" => $type,
            "offset"   => $offset,
            "count"   => $count,
        ];
        $json = json_encode($data);
        $result = net_req($url,$json,'POST',[
            'Content-Type: application/x-www-form-urlencoded'
        ],true);
        $result = json_decode($result,true);
        if(empty($result) || isset($result['errcode'])){
            //异常
            return false;
        }
        return $result;
    }

}