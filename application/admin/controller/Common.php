<?php
namespace app\admin\controller;

use think\Controller;

class Common extends Controller
{
    //场景
    const SCENE='admin';
    //当前登录者登录平台  0:平台,大于0:代理者登录
    protected $proxy_id = 0;
    //登录者id
    protected $user_id = 0;
    //页面是否需要登录
    protected $need_login = true;
    //忽略登录页面操作
    protected $ignore_login_action = '';

    protected function initialize()
    {
        if(session('?admin_user_info')){
            $this->user_id = session('admin_user_info.id');
            $this->proxy_id = session('admin_user_info.proxy_id');
        }
        $current_action = strtolower($this->request->action());
        //验证页面是否需要登录
        if($this->need_login && strpos(strtolower($this->ignore_login_action),$current_action)===false && !$this->user_id) {
            if($this->request->isAjax()){
                header('content-type:application/json; charset=utf-8');
                echo json_encode(['code'=>-1,'msg'=>'请先登录','url'=>url('Login/login')]);exit;
            }else{
//                header('Location:'.url('login/login',[],true,true));
//                $this->redirect('Login/login');
                echo '<script>window.parent.location.href="'.url('login/login',[],true,true).'"</script>';exit;

            }
        }
        //当前登录者/操作者
        bind('auth_user_id',function(){
            return $this->user_id;
        });
        //当前登录者/操作者
        bind('auth_proxy_id',function(){
            return $this->proxy_id;
        });

    }
}