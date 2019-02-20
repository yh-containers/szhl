<?php
namespace app\common\model;

class UserWithdraw extends Base
{
    protected $name='user_withdraw';

    public static $fields_status = ['状态异常','待审核','已通过','已被据'];

    public function setCidAttr($value,$data)
    {
        if($value){
            $model = new UserBankCard();
            $model = $model->get($value);
            $this->setAttr('name',$model['name']);
            $this->setAttr('card',$model['card']);
            $this->setAttr('bank_card',$model['bank_card']);
            $this->setAttr('bank_card_name',$model['bank_card_name']);
            $this->setAttr('rec_name',$model['rec_name']);
        }
        return $value;
    }

    //提现说明
    public static function getMsg()
    {
        $model = new Setting();
        $content = $model->getContent('withdraw');
        $content = json_decode($content,true);
        $times = !empty($content['times'])?$content['times']:0;
        $start_money = !empty($content['start_money'])?$content['start_money']:0;
        $end_money = !empty($content['end_money'])?$content['end_money']:0;
        return '尊敬的用户！当日提现'.($times?'不能超过'.$times.'次':'不限次数')
        .', 提现额度'
        .((empty($start_money) && empty($end_money))?'不限制额度':(''
        .($start_money?'最低'.$start_money.'元':'')
        .($end_money?', 最高'.$end_money.'元':'')))
            ;
    }

    //用户提现
    public function draw($user_id,$data)
    {
        $data['uid']= $user_id;
        $validate = new \app\common\validate\UserWithdraw();
        if (!$validate->check($data)) {
            abort(4000,$validate->getError());
        }

        try{
            $this->startTrans();
            //扣除用户余额
            Users::modMoney($user_id,-$data['money'],'用户提现');
            //保存数据
            $this->save($data);

            $this->commit();
        }catch (\Exception $e) {
            $this->rollback();
            abort(4000,$e->getMessage());
        }
        return true;
    }

    /*
     * 审核提现信息
     * */
    public function handleDraw($id,$data)
    {
        empty($id) && abort(4000,'操作对象异常');
        empty($data['status']) && abort(4000,'审核状态异常');
        $model = $this->get($id);
        $model['status']!=1 && abort(4000,'该申请未处于审核状态，无法操作');
        if($data['status']==2) {
            //通过
            $model->data($data);
            $model->opt_time= time();
            return $model->save();
        }
        //拒绝
        try{
            $this->startTrans();
            //扣除用户余额
            Users::modMoney($model['uid'],$model['money'],'提现被拒，返还');
            //保存数据
            $model->data($data);
            $model->opt_time= time();
            $model->save();
            $this->commit();
        }catch (\Exception $e) {
            $this->rollback();
            abort(4000,'操作异常:'.$e->getMessage());
        }
        return true;
    }

    //关联用户
    public function linkUserInfo()
    {
        return $this->belongsTo('Users','uid');
    }
}