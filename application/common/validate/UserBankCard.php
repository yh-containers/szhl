<?php
/**
 * Created by PhpStorm.
 * User: Ru
 * Date: 2019/2/15
 * Time: 14:34
 */

namespace app\common\validate;
use think\Validate;

class UserBankCard extends Validate
{

    protected $rule = [
        'name'          => 'require',
        'card'          => 'require|idCard',
        'bank_card'     => 'require',
        'bank_card_name'=> 'require',
        'rec_name'      => 'require',
    ];

    protected $message = [
        'name.require'          => '请输入真实姓名',
        'card.require'          => '请输入本人身份证号码',
        'card.idCard'           => '身份证号码异常',
        'rec_name.require'      => '请输入收款户名',
        'bank_card.require'     => '请输入银行卡号',
        'bank_card_name.require'=> '请输入银行卡名',

    ];

}