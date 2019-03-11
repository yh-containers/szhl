<?php
namespace app\index\widget;

use think\Controller;

class PageCrumb extends Controller
{
    //城市选择
    public function cityLocation()
    {
        $cache_name = 'PageCrumb/cityLocation';
        $data = cache($cache_name);
        $model = new \app\common\model\Location();
        if(!$data){
            $data = $model->field('*,left(area_py_f,1) as `py_index`')->where('area_type',2)->select()->toArray();
            //缓存数据
            $data = arr_field_group($data,'py_index');
            cache($cache_name,$data);

        }

        //热门城市
        $hot_location = $model->where('is_hot',1)->select();

        return $this->fetch('/widget/cityLocation',[
            'city_location' => $data,
            'hot_location' => $hot_location,
        ]);
    }

    //《中瀚哲服务协议》
    public function serviceProtocol()
    {
        $model = new \app\common\model\Setting();
        $content = $model->getContent('protocol');
        return $this->fetch('/widget/serviceProtocol',[
            'protocol_content' => $content,
        ]);
    }

    //获取系统配置信息
    public function getSettingInfo($type,$filed='')
    {
        $model = new \app\common\model\Setting();
        $content = $model->getContent($type);
        if($filed){
            $content = json_decode($content,true);
            $content = isset($content[$filed])?$content[$filed]:'';
        }
        return $content;
    }

    //底部数据
    public function footer($active='index')
    {
        return $this->fetch('/widget/footer',[
            'active' => $active
        ]);
    }


    //微信数据
    public function wechatJssdkConfig($url,$jsApiList=[])
    {
        try{
            $data = \app\common\service\wechat\Jssdk::sign($url);
            return $this->fetch('/widget/wechatJssdkConfig',[
                'data'      => $data,
                'jsApiList' => $jsApiList
            ]);
        }catch (\Exception $e){
            echo $e->getMessage();
        }

    }

    public function wechatJssdk()
    {

        return $this->fetch('/widget/wechatJssdk',[
        ]);
    }

    //选项
    public function chooseRow($data,$key='')
    {
        return $this->fetch('/widget/chooseRow',['data'=>$key?$data[$key]:$data]);
    }

    //选项
    public function chooseItem($data)
    {
        return $this->fetch('/widget/chooseItem',['data'=>$data]);
    }
}