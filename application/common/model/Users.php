<?php
namespace app\common\model;


class Users extends Base
{
    protected $name='users';

    protected $insert = ['face'=>'/uploads/face/toux.png','type'];
    protected $auto = ['join_time'];

    public static $fields_type=['普通用户','合作伙伴','代理用户'];


    //设置用户类型
    public function setTypeAttr($value,$data)
    {
        return $value?$value:0;
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
            //保存登录信息
            session($session_name,[
                'user_id' => $this->getData('id'),
                'type' => $this->getData('type'),
            ]);
        }

    }

    //修改用户余额
    public static function modMoney($user_id,$money,$intro='',$extra=[])
    {
        $model = new self();
        $model = $model->get($user_id);
        empty($model) && abort(4000,'用户信息异常');
        //更新用户余额
        $model->money	= [$money>0?'inc':'dec', abs($money)];
        //附加数据
        $model->setAttr('mod_money',$money); //变动余额
        $model->setAttr('mod_intro',$intro); //说明
        $model->setAttr('mod_extra',$extra); //扩展数据
        $model->save();
    }

    //记录余额变动日志
    public static function init()
    {
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

}