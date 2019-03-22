<?php
namespace app\index\controller;

class Index extends Common
{
    protected $need_login = true;
    protected $ignore_login_action = 'repair,identity,login,reg,forget,sendSms,changeLocation,test';

    protected $beforeActionList = [
        'checkWxAuthLogin',
    ];

    public function checkWxAuthLogin()
    {
        if(session('?auth_info_wx') && !session('?is_check_wx_login')){
            //标记已检测微信登录状态
            session('is_check_wx_login',1);
            //微信直接登录
            $auth_info_wx= session('auth_info_wx');
            if(!empty($auth_info_wx['openid'])){
                try{
                    $model =  new \app\common\model\Users();
                    $user_model = $model->where(['openid'=>$auth_info_wx['openid']])->find();
                    if(!empty($user_model)){
                        $user_model->handleLoginInfo();
                        $this->redirect(url('index/index'));
                    }
                }catch (\Exception $e){

                }


            }
        }

    }

    //维护页面
    public function repair()
    {
//        dump($_SESSION);exit;
        return view('repair',[

        ]);
    }

    //选择身份
    public function identity($is_choose=0)
    {
        if($this->user_id && !$is_choose){
            //表名用户已登录状态--直接跳转首页
            $this->redirect('index/index');
        }
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
            $page = 'proxy';

        }else{
            $page = 'homeMember';

        }
        if($this->proxy_id){
            $model_product = $model_product->withJoin(['linkProxy'],'left')->where('Product.status',1)->where('linkProxy.status',1)->where('linkProxy.proxy_id',$this->proxy_id);
        }else{
            $model_product = $model_product->where('status',1);
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
                cookie('identity_type',null);
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

    //
    public function applyConfirm()
    {
        return view('applyConfirm');
    }


    /*
     * 产品检索功能
     * */
    public function match()
    {
        //查询已选的属性
        $one =$one_data = $two = $two_data = $three = $three_data=  [];
        $model_setting = new \app\common\model\Setting();
        $content = $model_setting->getContent('user_search');
        $choose_item = $content?json_decode($content,true):[];
        $one = isset($choose_item[0])?$choose_item[0]:[];
        $two = isset($choose_item[1])?$choose_item[1]:[];
        $three = isset($choose_item[2])?$choose_item[2]:[];
        if($one) {
            $one_data=(new \app\common\model\ProductSpuCol())->whereIn('id',$one)->order('sort','asc')->select()->toArray();
            foreach ($one_data as &$vo) {
                foreach ($vo['content'] as &$item){
                    $item['spu_type'] = 0;      //spu_coll的id
                    $item['spu_sc_id'] = $vo['id'];      //spu_coll的id
                }
                $vo['content'] = arrayToTree2($vo['content']);

            }
        }
        if($two) {
            $two_data=(new \app\common\model\ProductSpuCol())->whereIn('id',$two)->order('sort','asc')->select()->toArray();
            foreach ($two_data as &$vo_two) {
                if(isset($vo_two['content'])){
                    foreach ($vo_two['content'] as &$item_two){
                        $item_two['spu_type'] = 0;      //spu_coll的id
                        $item_two['spu_sc_id'] = $vo_two['id'];      //spu_coll的id
                    }
                    $vo_two['content'] = arrayToTree2($vo_two['content']);
                }

            }
        }
        if($three) {
            $three_data=(new \app\common\model\ProductSpuCol())->whereIn('id',$three)->order('sort','asc')->select()->toArray();
            foreach ($three_data as &$vo_three) {
                if(isset($vo_three['content'])){
                    foreach ($vo_three['content'] as &$item_three){
                        $item_three['spu_type'] = 0;      //spu_coll的id
                        $item_three['spu_sc_id'] = $vo_three['id'];      //spu_coll的id
                    }
                    $vo_three['content'] = arrayToTree2($vo_three['content']);
                }

            }
        }
//        dump($one_data);exit;
        return view('match',[
            'one_data' => $one_data,
            'two_data' => $two_data,
            'three_data' => $three_data,
        ]);
    }

    public function test()
    {
//        dump($_SESSION);
//        dump(\app\common\service\wechat\Jssdk::sendMsg($openid,$content));exit;

//        $model = new \app\common\model\Users();
//        $model = $model->get(1);
//        dump($model->getTicket());
        //获取二维码
//        dump(\app\common\service\wechat\Jssdk::qrcode(1));exit;
//        $wechant_jssdk

    }

}
