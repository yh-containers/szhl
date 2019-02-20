<?php
/**
 * Created by PhpStorm.
 * User: Ru
 * Date: 2019/2/15
 * Time: 14:34
 */

namespace app\common\validate;
use think\Validate;

class UserWithdraw extends Validate
{

    protected $rule = [
        'uid'          => 'require',
        'cid'          => 'require',
        'money'        => 'require|checkMoney',
    ];

    protected $message = [
        'uid.require'           => '用户信息异常',
        'cid.require'           => '请选择银行卡',
        'money.require'         => '请输入提现金额',


    ];

    public function checkMoney($value,$rule,$data=[])
    {
        if(!is_numeric($value) || $value<=0 ){
            return '请输入正确的提现金额';
        }
        $model_users = new \app\common\model\Users();
        $model_users = $model_users->get($data['uid']);
        if(empty($model_users)){
            return '用户信息异常';

        }elseif($model_users['money']<$value){
            return '用户余额不足，无法提现';

        }
        $model_bank_card = new \app\common\model\UserBankCard();
        $model_bank_card = $model_bank_card->get($data['cid']);
        if(empty($model_bank_card) || $model_bank_card['uid'] !=$data['uid']){
            return '卡号异常';
        }
        //获取提现规则
        $model_setting = new \app\common\model\Setting();
        $setting = $model_setting->getContent('withdraw');
        $setting = json_decode($setting,true);
        if(!empty($setting['times'])){
            $model_withdraw = new \app\common\model\UserWithdraw();
            $times_count = $model_withdraw->where([['uid','=',$data['uid']],['create_time','egt',strtotime(date('Y-m-d'))]])->count();
            if($times_count>$setting['times']){
                return '已超出当日提现次数';
            }
        }

        if(!empty($setting['start_money']) && $setting['start_money']>$data['money']){
            return '提现金额不得低于最低额度';
        }

        if(!empty($setting['end_money']) && $setting['end_money']<$data['money']){
            return '提现金额不得高于最高额度';
        }



        return true;

    }

}