<?php
namespace app\index\controller;

class Wechat extends Common
{
    private $EncodingAESKey= 'paqx6ucXfCrnZr96ieqEgWkVgaKHDqmCU7Q6O7srCla';
    private $token = 'zhz';

    public function index()
    {
        if($this->request->isGet()){
            //验证流程
            return $this->request->get('echostr');
        }
        $php_input = file_get_contents('php://input');
        $data = $this->xmlToArray($php_input);
//        dump($data);exit;
//        cache('xml_test_input',$data);
        if(!empty($data) && is_array($data)){
            //消息类型
            $type = $data['MsgType'];
            $return_data = '';
            switch ($type){
                case 'event':
                    $return_data = $this->handleEvent($data);
                    break;
                case 'text':
//                    $return_data = $this->handleText($data);
                    break;
                default:
                    break;
            }
            return $return_data?$return_data:'success';
        }
//        $response_str = $this->handleResponse($data['FromUserName'],$data['ToUserName'],$data['Content']);

        return 'success';


    }
    public function test()
    {
        print_r('return_data:'.cache('return_data'));
//        dump(cache('wechat_post'));
//        dump(cache('xml_test_input'));
        return $this->handleResponse('gh_f4f0cf102e76','oCLVswdko6mhVcDPiscGTX9TDMvM','wechat_content');
//        return response(,200,[],'xml');
    }

    //处理事件通知
    public function handleEvent($data)
    {
        //事件类型
        $event = $data['Event'];
        if($event=='subscribe'){
            //关注/取消关注
            if(isset($data['EventKey'])){
                if(is_string($data['EventKey'])){
                    //二维码扫描关注--获取二维码值
                    $req_user_id = str_replace('qrscene_','',$data['EventKey']);
                    (new \app\common\model\UserWechatSubscribe())->save([
                        'openid' => $data['FromUserName'],//被邀请者
                        'req_user_id' => $req_user_id,  //邀请者用户id
                    ]);
                    $model = new \app\common\model\Setting();
                    $wechat_setting = $model->getContent('wechat_setting');
                    $content = isset($wechat_setting['follow'])?$wechat_setting['follow']:'';
                    return $content?$this->handleResponse($data['ToUserName'],$data['FromUserName'], $content):'';
                }

            }

        }elseif($event=='LOCATION'){
            //上传地址
        }elseif($event=='CLICK'){
            //自定义菜单事件
        }
    }

    //处理文本事件
    public function handleText($data)
    {
        return $this->handleResponse($data['ToUserName'],$data['FromUserName'],$data['Content']);
    }

    /*
     * 消息
     * @param $FromUserName string 发送者
     * @param $ToUserName string 接收者
     * @param $type string 消息类型
     * return array
     * */
    private function handleResponse($FromUserName,$ToUserName,$content,$type='text')
    {
        $time = time();
        if($type=='text'){
            $str = <<<EOT
<xml><ToUserName><![CDATA[$ToUserName]]></ToUserName><FromUserName><![CDATA[$FromUserName]]></FromUserName><CreateTime>$time</CreateTime><MsgType><![CDATA[$type]]></MsgType><Content><![CDATA[$content]]></Content></xml>
EOT;
            return $str;
        }
    }

    //将XML转为array
    private function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}


