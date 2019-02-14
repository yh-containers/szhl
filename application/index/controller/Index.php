<?php
namespace app\index\controller;

class Index extends Common
{
    public function index()
    {
        return view('index',[

        ]);
    }

    //用户登录
    public function login()
    {
        if($this->request->ISAJAX()){
            $mode = $this->request->param('mode',0,'intval');
            $php_input = $this->request->param();
            $model =  new \app\common\model\Users();
            try{
                if($mode==1){
                    //验证码登录
                    $model->verifyLogin($php_input);
                }else{
                    //帐号密码登录
                    $model->pwdLogin($php_input);
                }
                return ['code'=>1,'msg'=>'登录成功','url'=>url('index/index')];
            }catch (\Exception $e) {
                return ['code'=>$e->getCode(),'msg'=>$e->getMessage()];
            }

        }

        return view('login',[

        ]);
    }

    //用户登录
    public function reg()
    {
        if($this->request->ISAJAX()){
            $php_input = $this->request->param();
            $validate = new \app\common\validate\Users();
            $validate->scene(self::SCENE.'_reg');
            $model =  new \app\common\model\Users();
            return $model->actionAdd($php_input,$validate);
        }
        return view('reg',[

        ]);
    }

    //忘记密码
    public function forget()
    {
        if($this->request->ISAJAX()){
            $phone = $this->request->param('phone');
            $password = $this->request->param('password');
            $qr_password = $this->request->param('qr_password');
            $verify = $this->request->param('verify');

            if(empty($phone)) return ['code'=>0,'msg'=>'请输入手机号码'];
            if(empty($password)) return ['code'=>0,'msg'=>'请输入密码'];
            if(empty($qr_password)) return ['code'=>0,'msg'=>'请输入确认密码'];
            if($qr_password!=$password) return ['code'=>0,'msg'=>'两次密码不一致'];
            if(empty($verify)) return ['code'=>0,'msg'=>'请输入验证码'];

            try{
                $model =  new \app\common\model\Users();
                $model->forgetPwd($phone,$password,$verify);
                return ['code'=>1,'msg'=>'找回成功','url'=>url('index/login')];
            }catch (\Exception $e) {
                return ['code'=>$e->getCode(),'msg'=>$e->getMessage()];
            }
        }
        return view('forget',[

        ]);
    }

    //发送验证码
    public function sendSms()
    {
        $phone = $this->request->param('phone');
        $type  = $this->request->param('type');
        try{
            \app\common\model\Sms::sendSms($phone, $type);
            return json(['code'=>1,'msg'=>'已发送，请注意查收']);
        }catch (\Exception $e){
            return json(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    //测试专用
    public function changeLocation()
    {
        $model = new \app\common\model\Location();
        $cursor = $model->where('area_type',3)->whereNull('area_py')->select();
        $pinyin = new \Overtrue\Pinyin\Pinyin();
        foreach($cursor as $location){
            $convert = $pinyin->permalink($location['area_name'],'-');
            $first = $pinyin->abbr($location['area_name']);
            $location->save([
                'area_py'=>strtoupper($convert),
                'area_py_f'=>strtoupper($first),
            ],['area_id'=>$location['area_id']]);
        }

    }
}
