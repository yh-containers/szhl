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
    //页面是否需要登录
    protected $need_login = false;
    //忽略登录页面操作
    protected $ignore_login_action = '';
    public function initialize()
    {
        if(session('?user_info')){
            $this->user_id = session('user_info.user_id');
            $this->user_type = session('user_info.type');
        }
        $current_action = strtolower($this->request->action());
        //验证页面是否需要登录
        if($this->need_login && strpos(strtolower($this->ignore_login_action),$current_action)===false && !$this->user_id) {
            if($this->request->isAjax()){
                echo json(['code'=>0,'msg'=>'请先登录','url'=>url('Index/login')]);exit;
            }else{
                $this->redirect('index/login');

            }
        }

    }
}