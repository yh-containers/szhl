<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class ProductReq extends Base
{
    use SoftDelete;
    public static $fields_status=['创建请求','创建申请','已完成'];
    public static $fields_face_status=['未面谈','已面谈'];
    public static $fields_auth_status=['未审核','已审核','拒绝'];
    public static $fields_send_award_status=['未发放','已发放'];
    public static $fields_is_contract=['未签合同','已签合同'];

    public static $fields_sex = ['未知','男','女'];
    public static $fields_marry = ['未婚','已婚'];
    public static $fields_vocation_type = ['自雇','标新'];
    public static $fields_is_cr = ['无','有'];
    public static $fields_is_house = ['无','有'];
    public static $fields_is_car = ['无','有'];
    public static $fields_is_warranty = ['无','有'];
    public static $fields_is_social_security = ['无','有'];
    public static $fields_is_accumulation_fund = ['无','有'];

    protected $name='product_req';
    protected $insert = ['status'=>0,'no'];
    protected $auto=['status'];
    protected $json = ['product_info','content','percent_data'];

    //状态切换
    public function setStatusAttr($value)
    {
        if($value==2){
            $this->setAttr('complete_time',time()); //完成时间
        }
        return $value?$value:0;
    }

    //设置申请单号
    public function setNoAttr($value)
    {
        //数据缓存一天
        $cache_name = 'product_req_no'.date('Y-m-d');
        $no = cache($cache_name);
        if(!$no){
            $no = 0;
        }
        $no=$no+1;
        cache($cache_name,$no,86400);
        return date('YmdHis').rand(10000,99999).$no;
    }

    //图片--设置
    public function setCardImgAttr($value,$data)
    {
        return $value?implode(',',$value):[];
    }

    //图片--获取
    public function getCardImgAttr($value,$data)
    {
        return $value?explode(',',$value):[];
    }

    public function setStepAttr($value,$data)
    {

        if($value==1 && empty($data['status'])) {
            //完成步骤1-创建申请
            $this->setAttr('status',1);
        }

        return $value;
    }

    public function setPidAttr($value,$data)
    {
        if($value){
            $model = new \app\common\model\Product();
            $model = $model->get($value);
            $this->setAttr('money_unit',$model['money_unit']);
            $this->setAttr('auth_unit',$model['auth_unit']);
            $this->setAttr('commission',$model['commission']);
            $this->setAttr('per',$model['per']);
            $this->setAttr('per_unit',$model['per_unit']);
            $this->setAttr('product_info',$model);
            $this->setAttr('p_tid',$model['type']);//项目类型

            //代理商的产品
            if(session('?proxy_pro_info')){
                //邀请代理产品数据
                $proxy_pro_info = session('proxy_pro_info');
                $proxy_pro_info_flip = array_flip($proxy_pro_info);
                if(in_array($value,$proxy_pro_info_flip)){
                    $this->setAttr('proxy_id',$proxy_pro_info_flip[$value]);
                }
            }

        }
        return $value;
    }

    //面谈属性
    public function setFaceUidAttr($value,$data)
    {
        if($value){
            $this->setAttr('face_status',1);
            $this->setAttr('face_time',time());
        }
        return $value;
    }

    //面谈属性
    public function setSendAwardStatusAttr($value,$data)
    {
        if($value){
            //已完成
            $this->setAttr('status',2);
        }
        return $value;
    }

    public static function init()
    {
        //新增完成后触发
        self::event('after_insert', function ($model) {
            $title = '创建申请请求';
            $intro = '创建申请请求';
            $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
            //创建提示消息
            UserMessage::recordMsg(1,$title,$intro,$model['uid'],0,['id'=>$model['id']]);
        });
        //新增完成后触发
        self::event('after_update', function ($model) {
            $status = $model->getOrigin('status');
            $face_status = $model->getOrigin('face_status');
            $auth_status = $model->getOrigin('auth_status');
            $send_award_status = $model->getOrigin('send_award_status');
            $is_contract = $model->getOrigin('is_contract');
            //申请状态更改
            if(isset($model['status']) && $model['status']!=$status && $model['status']==1){
                $title = '提交申请成功';
                $intro = '申请额度'.$model['money'].Product::$money_unit[$model['money_unit']].',期限'.$model['auth_time'].Product::$auth_unit[$model['auth_unit']];
                $model->linkLogs()->saveAll([
                    ['title'=>$title,'intro'=>$intro],
                    ['title'=>'审批中','intro'=>'您的贷款申请已提交成功！'],
                ]);
                //创建提示消息
                UserMessage::recordMsg(1,$title,$intro,$model['uid'],0,['id'=>$model['id']]);

            }elseif(isset($model['status']) && $model['status']!=$status && $model['status']==2){
                $title = '交易完成';
                $intro = '交易已结束，感谢您的使用';
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);

                //创建提示消息
                UserMessage::recordMsg(1,$title,$intro,$model['uid'],0,['id'=>$model['id']]);

            }

            //面谈
            if(isset($model['face_status']) &&  $model['face_status']!=$face_status && $model['face_status']==1){
                $title = '面谈完成';
                $intro = $model['face_content'];
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
                //创建提示消息
                UserMessage::recordMsg(1,$title,$intro,$model['uid'],0,['id'=>$model['id']]);
            }
            //审核
            if(isset($model['auth_status']) &&  $model['auth_status']!=$auth_status && $model['auth_status']==1){
                $title = '恭喜您通过审核';
                $intro = $model['auth_content']?$model['auth_content']:'您的贷款申请已审核通过，客服人员将与你预约面谈';
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
                //创建提示消息
                UserMessage::recordMsg(1,$title,$intro,$model['uid'],0,['id'=>$model['id']]);

            }elseif(isset($model['auth_status']) &&  $model['auth_status']!=$auth_status && $model['auth_status']==2){
                $title = '审核被拒';
                $intro = $model['auth_content']?$model['auth_content']:'非常抱歉，您申请的贷款未放款成功！';
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
                //创建提示消息
                UserMessage::recordMsg(1,$title,$intro,$model['uid'],0,['id'=>$model['id']]);
            }elseif(isset($model['send_award_status']) && $model['send_award_status']!=$send_award_status && $model['send_award_status']==1){
                $title = '申请额度已发放';
                $intro = $model['auth_content']?$model['auth_content']:'恭喜您，您申请的贷款已成功放款！';
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
                //创建提示消息
                UserMessage::recordMsg(1,$title,$intro,$model['uid'],0,['id'=>$model['id']]);
                //创建提示消息
                UserMessage::recordMsg(3,'成功借款','恭喜！'.substr_replace($model['phone'],'*',3,4).'的用户成功借款'.$model['money'].Product::$money_unit[$model['money_unit']],$model['uid']);

            }elseif(isset($model['is_contract']) && $model['is_contract']!=$is_contract && $model['is_contract']>0){
                $title = '签订合同';
                $intro = '签订合同成功';
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
                //创建提示消息
                UserMessage::recordMsg(1,$title,$intro,$model['uid'],0,['id'=>$model['id']]);

            }elseif(isset($model['p_auth_mid']) && $model['p_auth_mid']!=$send_award_status && $model['p_auth_mid']>0){
//                $model_manage = new Manage();
//                $model_manage = $model_manage->get($model['p_auth_mid']);
//                $title = '指派审核员';
//                $intro = '指派审核员:用户名'.$model_manage['name'].'帐号:'.$model_manage['account'];
//                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);

            }elseif(isset($model['status']) && $model['status']==$status){
//                $title = '更新资料';
//                $model->linkLogs()->save(['title'=>$title,'intro'=>'更新申请资料']);

            }
        });
    }

    /*
     * 获取流程信息
     * @param $step int 流程步骤
     * @param $pid int 项目id
     * @param $id int 项目申请主键id
     * */
    public function getStepInfo($step,$pid,$id=0)
    {

    }

    /*
     * 申请审核
     * @param $id int 申请id
     * @Param $user_id int 用户id
     * @param $auth_status int 审核状态
     * @param $auth_content string 审核内容
     * */
    public function authReq($id,$user_id,$auth_status,$auth_content)
    {
        $model = $this->get($id);
        empty($model) && abort(4000,'操作对象异常');
        $model['auth_status']!=0 && abort(4000,'操作对象未处于审核状态,无法进行此操作');

        $model->auth_uid = $user_id;
        $model->auth_status = $auth_status;
        $model->auth_content = $auth_content;
        //拒绝
        if($auth_status==2){
            $model->status = 2; //标记申请已完成
        }


        $state = $model->save();
        return $state;

    }

    /*
     * 发放奖金
     * @param $id int 申请id
     * @Param $user_id int 用户id
     * */
    public function sendAward($id,$user_id)
    {
        $model = $this->get($id);
        empty($model) && abort(4000,'操作对象异常');
        $model['auth_status']!=1 && abort(4000,'该申请未通过审核，无法发放奖金');
        $model['send_award_status']!=0 && abort(4000,'操作对象未处于发放奖励状态,无法进行此操作');

        $model->auth_uid = $user_id;
        $model->send_award_status = 1;
        $model->send_award_time = time();
        $state = true;

        //获取申请者用户信息
        $model_user = new Users();
        $model_user = $model_user->get($model['uid']);
        empty($model_user) && abort(4000,'用户信息异常');
        $commission_users = $model_user->getCommissionPer();
        //通过
        try{
            $this->startTrans();
            //保存数据
            $model->save();
            //增加分佣操作
            $money_unit_transform = Product::moneyUnit($model['money_unit']); //单位换算
            $commission_money = $model['money']*$money_unit_transform*$model['commission']*0.01; //佣金
            if($commission_users){
                $commission_data = [];
                foreach($commission_users as $vo){
                    $commission_data[] = [
                        'type'  => 1,
                        'uid'   => $vo['user_id'],
                        'o_uid' => $model['uid'],
                        'money' => $commission_money*$vo['per'],
                        'extra' => [
                            'id' => $id,
                            'pid'=> $model['pid'],
                        ]
                    ];
                }
                $model_money_source = new UserMoneySource();
                $model_money_source->saveAll($commission_data);
            }
            //按期设置还款计划
            $model->handlePlan();

            $this->commit();
            //执行发放奖励
            Users::sendMoney();
        }catch (\Exception $e) {
            $state = '操作异常:'.$e->getMessage();
            $this->rollback();

        }

        return $state;
    }

    //处理项目还款计划
    public function handlePlan()
    {
        //当前时间
        $time = time();
        //还款金额
        //增加分佣操作
        $money_unit_transform = Product::moneyUnit($this->getData('money_unit')); //单位换算
        $money = $this->getData('money')*$money_unit_transform;
        //还款利润
        $per = $this->getData('per')*Product::authUnit($this->getData('per'));
        //按月处理分期--还款多少次
        $auth_times = $this->getData('auth_time');
        //每月还款额度
        $money_month = $auth_times?intval($money/$auth_times*100)/100:0;
        //每月还款利息
        $profit_money_month = $money*$per;
        //还款期数总额
        $profit_money = $profit_money_month*$auth_times;
        //还款总金额
        $total_money = $money + $profit_money;
        //余额计数器
        $register_money = 0;
        $data = [];
        for($i=0;$i<$auth_times;$i++){
            $times_money = ($auth_times-1==$i)?($money-$register_money) :$money_month;
            $times_total_money = $profit_money_month+$times_money; //每次还款总额度
            $data[] = [
                'uid'           => $this->getData('uid'),//还款用户
                'req_id'        => $this->getKey(),//项目id
                'total_money'   => $times_total_money,
                'money'         => $times_money,
                'profit_money'  => $profit_money_month,//每次利息
                'date'          => date('Y-m-d',strtotime('+'.($i+1).' month',$time)),
            ];
            //记录已处理余额
            $register_money += $times_money;
        }

        if($data){
            $model = new UserProductPlan();
            return $model->saveAll($data);
        }
        return false;
    }

    //获取产品状态
    public function getStatusInfo()
    {
        $status_info = [1,'审核中','审核中'];
        if($this['auth_status']==2){
            $status_info = [2,'审核被拒',$this['auth_content']?$this['auth_content']:'审核被拒'];

        }elseif ($this['auth_status']==1 && $this['face_status']==1 && $this['send_award_status']==0){
            $status_info = [3,'待放款','待发款'];

        }elseif ($this['auth_status']==1 && $this['face_status']==1 && $this['send_award_status']==1){
            $status_info = [4,'已放款','已放款'];

        }elseif($this['auth_status']>0 && $this['face_status']==0){
            $status_info = [5,'面谈中',$this['auth_content']?$this['auth_content']:'面谈中'];

        }

        return $status_info;
    }

    //产品签订合同
    public function contract($id,$opt_uid,$mine_opt_uid=0)
    {
        $model_info = $this->get($id);
        empty($model_info) && abort(4000,'操作对象异常');
        !empty($model_info['is_contract']) && abort(4000,'已签订合同，无法再次操作');
        //获取用户信息
        $model_user = (new Users())->where('id',$model_info['uid'])->find();
        empty($model_info) && abort(4000,'用户资料异常');

        try{
            $model_info->is_contract=1;
            $model_info->contract_opt_id=$opt_uid;
            $model_info->mine_opt_uid=$mine_opt_uid;//自己同意合同
            $model_info->contract_time=time();
            //模版内容
            $model_info->contract_content=\app\common\service\temp\Contract::changeContent($model_user);
            $model_info->save();
        }catch (\Exception $e){
            abort(4000,$e->getMessage());
        }

    }


    //关联日志
    public function linkLogs()
    {
        return $this->hasMany('ProductReqLogs','req_id')->order('id','desc');
    }

    //还款计划
    public function linkPlan()
    {
        return $this->hasMany('UserProductPlan','req_id');
    }
    //关联项目
//    public function linkProduct()
//    {
//        return $this->belongsTo('Product','pid');
//    }
    //关联用户
    public function linkUsers()
    {
        return $this->belongsTo('Users','uid');
    }
}