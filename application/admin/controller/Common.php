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
    protected $user_id = 2;

    protected function initialize()
    {
        //当前登录者/操作者
        bind('auth_user_id',function(){
            return $this->user_id;
        });
    }
}