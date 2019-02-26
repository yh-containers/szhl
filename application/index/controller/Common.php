<?php
namespace app\index\controller;

use think\Controller;

class Common extends Controller
{
    //场景
    const SCENE='index';

    //当前登录者id
    protected $user_id = 0;

    //当前登录者类型
    protected $user_type = 0;
    //用户所属代理商
    protected $proxy_id = 0;
    //页面是否需要登录
    protected $need_login = false;
    //忽略登录页面操作
    protected $ignore_login_action = '';
    public function initialize()
    {
        //邀请者用户id
        $this->request->has('req_user_id') && session('req_user_id',$this->request->param('req_user_id',0,'intval'));
        //邀请用户购买产品
        $proxy_pro_id = $this->request->param('proxy_pro_id',0,'intval');
        $id = $this->request->param('id',0,'intval');
        if($proxy_pro_id && $id){
            $proxy_pro_info = session('proxy_pro_info');
            $proxy_pro_info = $proxy_pro_info?$proxy_pro_info:[];
            //判断是否已记录过
            if(!array_key_exists($proxy_pro_id,$proxy_pro_info)){
                                //代理产品id    产品id
                $proxy_pro_info[$proxy_pro_id] = $id;
                session('proxy_pro_info',$proxy_pro_info);
            }
        }

//        dump(session('req_user_id'));exit;
        if(session('?user_info')){
            $this->user_id = session('user_info.user_id');
            $this->user_type = session('user_info.type');
            $this->proxy_id = session('user_info.proxy_id');
        }
        $current_action = strtolower($this->request->action());
        //验证页面是否需要登录
        if($this->need_login && strpos(strtolower($this->ignore_login_action),$current_action)===false && !$this->user_id) {
            if($this->request->isAjax()){
                header('content-type:application/json; charset=utf-8');
                echo json_encode(['code'=>-1,'msg'=>'请先登录','url'=>url('Index/identity')]);exit;
            }else{
                $this->redirect('index/identity');
            }
        }

    }
}