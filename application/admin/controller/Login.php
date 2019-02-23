<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/13
 * Time: 10:43
 */

namespace app\admin\controller;
use think\captcha\Captcha;
use think\Controller;
use think\Validate;
use think\Request;
use think\Db;
use think\Session;
/**
 *  登录模块
 *  用户访问页面，执行魔术方法，校验用户是否处于登录状态 Token登录机制
 *      用户登录过时
 *      用户账号被禁用
 *      用户已登录
 *      用户已退出
 *
 */
class Login extends Common
{

    protected $need_login = false;

    public function index()
    {

        return $this -> fetch();
    }
    public function login()
    {

        // 登录

        if($this->request->isAjax()){
            $model = new \app\common\model\Manage();
            $php_input = $this->request->param();
            if(!$php_input['account'] || !$php_input['password']){ return ['code'=>0,'msg'=>'账号或密码不能为空！']; }

            $captcha = new Captcha();
            if( !$captcha->check($php_input['code'])) { return ['code'=>0,'msg'=>'验证码错误']; }
            $user = $model->where(array('account'=>$php_input['account']) )->find();

            if(empty($user)){  return ['code'=>0,'msg'=>'账号或密码不正确！']; }

            $pwd = \app\common\model\Manage::entryPwd($php_input['password'],$user['slat']);

            if($pwd != $user['password']) {  return ['code'=>0,'msg'=>'账号或密码不正确！']; }


            if($user['status'] != 1){ return ['code'=>0,'msg'=>'账号已被禁用！']; }
            //获取用户角色
            $role_info = $user->linkRole;
            $this->_handleLoginInfo($user['id'],$user['name'],$user['proxy_id']?$user['id']:0,$user['rid'],$role_info['name']);

            return ['code'=>1,'msg'=>'登录成功！','url'=>url('index/index')];
        }
        return view('login');

    }

//    public function proxyLogin()
//    {
//
//        // 登录
//
//        if($this->request->isAjax()){
//            $model = new \app\common\model\Users();
//            $php_input = $this->request->param();
//            if(!$php_input['account'] || !$php_input['password']){ return ['code'=>0,'msg'=>'账号或密码不能为空！']; }
//
//            $captcha = new Captcha();
//            if( !$captcha->check($php_input['code'])) { return ['code'=>0,'msg'=>'验证码错误']; }
//            $user = $model->where(array('phone'=>$php_input['account']) )->find();
//
//            if(empty($user)){  return ['code'=>0,'msg'=>'账号或密码不正确！']; }
//
//            $pwd = \app\common\model\Users::entryPwd($php_input['password'],$user['salt']);
//
//            if($pwd != $user['password']) {  return ['code'=>0,'msg'=>'账号或密码不正确！']; }
//
//
//            if(empty($user['proxy_id'])) return ['code'=>0,'msg'=>'您不是代理商用户，无法登录'];
//
//            $this->_handleLoginInfo($user['id'],$user['name'],$user['proxy_id']);
//            return ['code'=>1,'msg'=>'登录成功！','url'=>url('index/index')];
//        }
//        return view('proxyLogin',[
//
//        ]);
//    }

    //处理登录信息
    private function _handleLoginInfo($id,$name,$proxy_id=0,$rid=0,$rid_name='')
    {
        session('admin_user_info', [
            'id'            =>  $id,
            'name'          =>  $name,
            'proxy_id'      =>  $proxy_id,
            'rid'           =>  $rid,
            'rid_name'      =>  $rid_name,
        ]);
    }


    public function logout()
    {
        session('admin_user_info',null);
        $this->redirect(url('login/login'));

    }

    //验证码
//    public function verify()
//    {
//        $captcha = new Captcha([
//            'codeSet' => '1234567890',
//        ]);
//        return $captcha->entry();
//    }
}