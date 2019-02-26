<?php
namespace app\index\controller;

class Index extends Common
{
    protected $need_login = true;
    protected $ignore_login_action = 'identity,login,reg,forget,sendSms,changeLocation';

    //选择身份
    public function identity()
    {
        return view('identity',[

        ]);
    }

    public function index()
    {
        //轮播图
        $flow_image = [];
        //文章列表
        $article_list = [];
        //滚动消息
        $flow_msg = [];
        //菜单栏
        $menu = \app\common\model\Product::$type_label;
        //热门产品
        $model_product = new \app\common\model\Product();

        //验证当前登录者是那种身份跳转对应页面
        if($this->user_type==1){

            $page = 'home';
        }elseif($this->user_type==2){
            $model_product = $model_product->withJoin(['linkProxy'],'left')->where('linkProxy.proxy_id',$this->proxy_id);
            $page = 'proxy';
        }else{


            $page = 'homeMember';
        }
        //轮播图
        $model_flow_image = new \app\common\model\FlowImage();
        $flow_image = $model_flow_image->where('status',1)->order('sort','asc')->select();
        $model_article = new \app\common\model\Article();
        $article_list = $model_article->order('sort','asc')->limit(3)->select();


        $product_list = $model_product->limit(5)->order('is_hot','asc')->select();
        //文章
        $model_message = new \app\common\model\UserMessage();
        $flow_msg = $model_message->where('type',3)->order('id','desc')->limit(10)->select();
        return view($page,[
            'user_type' => $this->user_type,
            'product_list' => $product_list,
            'flow_image' => $flow_image,
            'article_list' => $article_list,
            'menu' => $menu,
            'flow_msg' => $flow_msg,
        ]);
    }

    //用户登录
    public function login()
    {
        if($this->request->has('type')){
            //处理选择--身份
            cookie('identity_type',$this->request->param('type',0,'intval'));
        }

        if(!cookie('?identity_type')){
            //身份没有选择跳去选择
            $this->redirect('index/identity');
        }


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

    //用户退出
    public function logout()
    {
        $model =  new \app\common\model\Users();
        $model->handleLoginInfo(true);
        $this->redirect('index/login');
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

    //借款攻略
    public function help()
    {
        $model = new \app\common\model\Setting();
        $content = $model->getContent('borrow_money');
        return view('help',[
            'content' => $content
        ]);
    }
}
