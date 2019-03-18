<?php
namespace app\common\model;


class Users extends Base
{
    protected $name='users';

    protected $insert = ['face'=>'/uploads/face/toux.png','type','fuid1','proxy_id','name'];
    protected $auto = ['join_time'];
    protected $json = ['wechat_qrcode'];

    public static $fields_type=['普通用户','合作伙伴','代理用户'];

    //设置邀请用户
    public function setFuid1Attr($value,$data)
    {
        //判断是否有邀请者
        if(session('?req_user_id') && session('req_user_id')){
            //查看邀请者的邀请者
            $model = new self();
            $value=session('req_user_id');
            $req_user_info = $model->where('id',$value)->find();
            //普通会员无法享受代理
            if($req_user_info && $req_user_info['type']>0){
                $fuid2 = $req_user_info['fuid1']?:0;
                $proxy_id = $req_user_info['proxy_id']; //邀请者代理商

                $fuid2 && $this->setAttr('fuid2',$fuid2);
                $proxy_id && $this->setAttr('proxy_id',$proxy_id);

            }
        }


        return $value?$value:0;
    }

    //设置用户类型
    public function setNameAttr($value,$data)
    {
        if(empty($value) && !empty($data['phone'])){
            $value = substr($data['phone'],-4).'用户';
        }
        return $value;
    }

    //设置用户属于哪个代理商
    public function setProxyIdAttr($value,$data)
    {
        return $value?$value:0;
    }


    //设置用户类型
    public function setTypeAttr($value,$data)
    {
        return $value?$value:(cookie('?identity_type')?(cookie('identity_type')!=2?cookie('identity_type'):0):0);
    }

    //设置加入时间
    public function setJoinTimeAttr($value,$data)
    {
        return $value?$value:time();
    }

    //设置城市
    public function setCityAttr($value,$data)
    {
        if(!isset($data['province'])){
            //获取省份
            $model =new \app\common\model\Location();
            $province = $model->where('area_id',$value)->value('parent_id');
            $this->setAttr('province', $province);
        }
        return $value;
    }
    //获取城市名
    public function getCityNameAttr($value,$data)
    {
        $name = '';
        if(isset($data['city'])){
            //获取省份
            $model =new \app\common\model\Location();
            $name = $model->where('area_id',$data['city'])->value('area_name');
        }
        return $name;
    }
    //设置密码
    public function setPasswordAttr($value)
    {
        $salt = rand(10000,99999);
        $this->setAttr('salt', $salt);
        return self::entryPwd($value,$salt);
    }

    //用户密码加密
    public static function entryPwd($pwd,$salt)
    {
        return md5($pwd.md5($pwd.$salt).$salt);
    }

    /*
     * 验证码登录
     * */
    public function verifyLogin($data)
    {
        empty($data['phone']) && abort(4000,'请输入手机号码');
        empty($data['verify']) && abort(4000,'请输入验证码');
        //验证验证码
        \app\common\model\Sms::checkVerify($data['phone'],$data['verify'],1);
        //验证成功
        $model = $this->where('phone',$data['phone'])->find();
        if(empty($model)) {
            //注册帐号
            $this->save([
                'phone' => $data['phone']
            ]);
            $model = $this;
        }
        //登录成功
        $model->handleLoginInfo();
    }

    /*
     * 帐号密码登录
     * */
    public function pwdLogin($data)
    {
        empty($data['phone']) && abort(4000,'请输入手机号码');
        empty($data['password']) && abort(4000,'请输入密码');

        $model = $this->where('phone','=',$data['phone'])->find();
        empty($model) && abort(4000,'用户名或密码错误');
        $entry_pwd = self::entryPwd($data['password'],$model['salt']);
        $entry_pwd!=$model['password'] && abort(4000,'用户名或密码错误.');
        $model->handleLoginInfo();
    }

    /*
     * web端登录成功处理登录信息
     * */
    public function handleLoginInfo($clear=false)
    {
        $session_name = 'user_info';
        if($clear){
            //清空缓存
            session($session_name,null);
        }else{
            $user_id = $this->getData('id');
            //保存登录信息
            session($session_name,[
                'user_id' => $user_id,
                'type' => $this->getData('type'),
                'proxy_id' => $this->getData('proxy_id'),
            ]);
            //微信授权信息
            $auth_info_wx = session('auth_info_wx');
            if(!empty($auth_info_wx)){
                //绑定微信号
                $state = (new self())->where('id',$user_id)->setField('openid',$auth_info_wx['openid']);
                //有影响就删除之前帐号关联的微信号
                $state && (new self())->where([['id','<>',$user_id],['openid','=',$auth_info_wx['openid']]])->setField('openid','');
            }

        }

    }

    //修改用户余额
    public static function modMoney($user_id,$money,$intro='',$extra=[],$is_record_history_money=true)
    {
        $model = new self();
        $model = $model->get($user_id);
        empty($model) && abort(4000,'用户信息异常');
        //更新用户余额
        $model->money	= [$money>0?'inc':'dec', abs($money)];
        !empty($model) && $is_record_history_money && $model->history_money=['inc', $money];
        //附加数据
        $model->setAttr('mod_money',$money); //变动余额
        $model->setAttr('mod_intro',$intro); //说明
        $model->setAttr('mod_extra',$extra); //扩展数据
        $model->save();
    }

    //记录余额变动日志
    public static function init()
    {
        //保存成功后
        self::event('after_insert',function($model){
           if(isset($model['type']) && $model['type']==2){ //代理用户
               $model->proxy_id=$model['id'];
               $model->save();
           }
           if(!empty($model['fuid1'])){
               //成功邀请用户
               //创建提示消息
               UserMessage::recordMsg(1,'成功邀请一位用户','',$model['fuid1']);
               //发送微信通知
               $openid = (new Users())->where('id',$model['fuid1'])->value('openid');
               $openid && \app\common\service\wechat\Jssdk::sendMsg($openid,'成功邀请一位用户');
           }
        });

        self::event('after_update', function ($model) {
            $money = $model->getOrigin('money');
            if(isset($model['money']) && $money!=$model['money']){
                $model->linkMoneyLogs()->save([
                    'origin_money' => $money,
                    'money'        => $model['mod_money'],
                    'new_money'    => $money+$model['mod_money'],
                    'intro'        => $model['mod_intro'],
                    'extra'        => $model['mod_extra'],
                ]);
            }
        });
    }

    /*
     * 获取用户分佣比例情况
     * */
    public function getCommissionPer()
    {
        $users = array_filter([$this->getData('fuid1'),$this->getData('fuid2')]);
        $data = [];
        $per = count($users)>1?[0.5,0.5]:[1];
        foreach ($users as $key=>$vo){
            $data[] =[
                'user_id' => $vo,
                'per'     => isset($per[$key])?$per[$key]:0,
            ];
        }
        return $data;
    }

    /*
     * 执行余额来源发放
     * */
    public static function sendMoney()
    {
        $time = time();
        //发放说明类
        $intro_class = ['','\\app\\common\\model\\Users'];
        $model = new UserMoneySource();
        $model->where([
            ['status','=',1],
            ['send_time','<=',time()]
        ])->select()->each(function($item,$index)use($intro_class,$time){
            try{
                //处理金额
                Users::modMoney($item['uid'],$item['money'],$intro_class[$item['type']]::getAwardIntro(),$item['extra']);
                //修改数据
                $item->status = 2;
                $item->send_time = $time;
                $item->save();
            }catch (\Exception $e){
                trace('发放奖金异常'.$e->getMessage(),'sendMoneyError');
            }
        });
    }

    /*
     * 奖金说明
     * */
    public static function getAwardIntro()
    {
        return '佣金奖励';
    }

    /*
     * 获取用户微信ticket
     * */
    public function getTicket()
    {
        $user_id = (int)$this->getData('id');
        if(!$user_id){
            return false;
        }
        if($this->wechat_qrcode){
            if((time()-$this->wechat_qrcode['create_time']) <   $this->wechat_qrcode['expire_seconds']){
                return $this->wechat_qrcode['url'];
            }
        }
        try{
            $ticket_info = \app\common\service\wechat\Jssdk::qrcodeUser($user_id);
            if($ticket_info===false){
                //异常
                return false;
            }else{
                list($ticket,$expire_seconds,$url) = $ticket_info;
                //缓存票据
                $this->wechat_qrcode= ['ticket'=>$ticket,'expire_seconds'=>$expire_seconds,'url'=>$url,'create_time'=>time()];
                $this->save();
                return $url;
            }
        }catch (\Exception $e){
            //异常--内部异常
            return false;
        }


    }

    /*
     * 忘记密码
     * */
    public function forgetPwd($phone,$pwd,$verify)
    {
        //验证验证码
        \app\common\model\Sms::checkVerify($phone,$verify,2);
        //验证成功
        $model = $this->where('phone',$phone)->find();
        $model->save(['password'=>$pwd],['phone'=>$phone]);
    }

    /*
     * 我邀请我的人--关联查询
     * */
    public function linkMineReq()
    {
        return $this->hasMany('Users','fuid1');
    }

    /*
     * 我邀请我的人--二级关联查询
     * */
    public function linkMineReqTwo()
    {
        return $this->hasMany('Users','fuid2');
    }

    /*
     * 邀请我的人--关联查询
     * */
    public function linkDirectFuid()
    {
        return $this->belongsTo('Users','fuid1');
    }

    /*
     * 余额变动日志
     * */
    public function linkMoneyLogs()
    {
        return $this->hasMany('UserMoneyLogs','uid');
    }

    /*
     * 贷款记录
     * */
    public function linkReqList()
    {
        return $this->hasMany('ProductReq','uid');
    }

}