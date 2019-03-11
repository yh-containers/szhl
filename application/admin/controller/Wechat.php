<?php
namespace app\admin\controller;

class Wechat extends Common
{
    //菜单栏
    public function menu()
    {
        $setting_key = 'wechat_menu';
        $model = new \app\common\model\Setting();
        if($this->request->isAjax() || $this->request->isPost()){
            //
            $menu = $this->request->param('menu');
            $menu = json_decode($menu,true);
            if(isset($menu['menu'])){
                //调整菜单栏
                $button = isset($menu['menu']['button'])?$menu['menu']['button']:[];
                //保存本地数据库

                foreach($button as &$vo) {
                    if($vo['type']=='view_limited'){
                        $vo['media_id'] = substr($vo['key'],4);
                    }
                }
//                dump($button);exit;
                try{
                    $state = \app\common\service\wechat\Jssdk::menu($button);
                    //直接入库
                    $model->setContent($setting_key,json_encode($button));

                    return ['code'=>(int)$state,'msg'=>$state?'操作成功':'操作异常'];
                }catch (\Exception $e) {
                    return ['code'=>0,'msg'=>'操作异常:'.$e->getMessage()];
                }
            }
            return ['code'=>0,'msg'=>'参数异常'];
        }
        //获取图文素材
        $material = \app\common\service\wechat\Jssdk::getMaterial();
        $material = isset($material['item'])?$material['item']:[];
//        dump($material);exit;
        //按钮信息
        $var_menu = $model->getContent($setting_key);
        $var_menu = $var_menu?json_decode($var_menu,true):[];
        return view('menu',[
            'var_menu' => $var_menu,
            'material' => $material,
        ]);
    }

    /*
     * 处理用户提现
     * */
    public function setting()
    {

        $model = new \app\common\model\Setting();
        $wechat_setting = $model->getContent('wechat_setting');
        return view('setting',[
            'wechat_setting' => json_decode($wechat_setting,true),
        ]);
    }

}
