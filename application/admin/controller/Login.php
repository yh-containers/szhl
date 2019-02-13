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
class Login extends Controller
{

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

//            if($slat->isEmpty()){
//                return ['code'=>0,'msg'=>'账号或密码不存在！'];
//            }else{
//                $slat = $slat->visible(['slat'=>['slat']])->toArray();
//                foreach ($slat as $slat_) {}
//                $pwd = \app\common\model\Manage::entryPwd($php_input['password'],$slat_['slat']);
//                $user = $model->where(array('account'=>$php_input['account'],'password'=>$pwd) )->find();


            if($user['status'] != 1){ return ['code'=>0,'msg'=>'账号已被禁用！']; }
            $user_session = array(
                'id'=>$user['id'],
                'name'=>$user['name'],
                'username'=> $user['account'],
            );
            session('userInfo', $user_session);
            return ['code'=>1,'msg'=>'登录成功！','url'=>url('/admin/index/index')];
//            }

        }
        return view('login');

    }

    public function logout()
    {
        session(null);
        $this->success('登出成功！',url('/admin/login/login'));

    }

    //验证码
    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }
}