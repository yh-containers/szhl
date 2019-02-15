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
        if(!$data){
            $model = new \app\common\model\Location();
            $data = $model->field('*,left(area_py_f,1) as `py_index`')->where('area_type',2)->select()->toArray();
            //缓存数据
            $data = arr_field_group($data,'py_index');
            cache($cache_name,$data);

        }
        return $this->fetch('/widget/cityLocation',[
            'city_location' => $data,
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
}