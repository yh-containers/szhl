<?php
/**
 * Created by PhpStorm.
 * User: Ru
 * Date: 2019/2/15
 * Time: 14:34
 */

namespace app\common\validate;
use think\Validate;

class ProductReq extends Validate
{

    protected $rule = [
        'id'               =>  'require|gt:0',
        'pid'              =>  'require|gt:0',
        'name'             =>  'require|max:25',
        'card'             =>  'require|idCard',
        'card_img'         =>  'require',
        'phone'            =>  'require|mobile',
        'sex'              =>  'require',
        'work_unit'        =>  'require',
        'is_marry'         =>  'require',
        'addr'             =>  'require|max:150',
        'vocation_type'    =>  'require',
        'is_cr'            =>  'require',
        'cr_times_two'     =>  'require',
        'credit_money'     =>  'require',
        'is_house'         =>  'require',
        'is_car'           =>  'require',
        'is_warranty'      =>  'require',
        'is_social_security'=>  'require',
        'is_accumulation_fund'=>  'require',

        'face_uid'         =>  'require',
        'face_content'     =>  'require|min:5',
    ];

    protected $message = [
        'pid.require'           => '产品信息异常',
        'pid.gt'                => '产品信息异常',
        'id.require'            => '流程步骤异常',
        'id.gt'                 => '流程步骤异常',
        'name.require'          => '请输入用户名',
        'name.max'              => '用户名不得超过:rule个字符',
        'card.require'          => '请输入身份证号码',
        'card.idCard'           => '身份证号码异常',
        'card_img.require'      => '请上传身份证图片',
        'phone.require'         => '请输入手机号码',
        'phone.mobile'          => '请输入正确的手机号码',
        'sex.require'           => '请选择性别',
        'work_unit.require'     => '请输入工作单位',
        'is_marry.require'      => '全选择婚姻状况',
        'addr.require'          => '请输入地址',
        'addr.max'              => '地址不得超过:rule个字符',
        'vocation_type.require' => '请选择职业类别',
        'is_cr.require'         => '请选择征信报告',
        'cr_times_two.require'  => '请输入近两个月征信查询次数',
        'credit_money.require'  => '信用贷款月还款总额',
        'is_house.require'      => '请选择是否有房',
        'is_car.require'        => '请选择是否有车',
        'is_warranty.require'   => '请选择是否有房',
        'is_social_security.require'      => '请选择是否有保单',
        'is_accumulation_fund.require'      => '请选择是否有公积金',

        'face_uid.require'      => '操作者信息异常',
        'face_content.require'  => '面谈内容必须输入',
        'face_content.min'      => '面谈内容必须超过:rule个字符',

    ];

    //申请步骤一
    public function sceneIndex_req0()
    {
        $this->only(['pid'])
            ->append('pid','checkStep:0')
            ;
    }

    //申请步骤二
    public function sceneIndex_req1()
    {
        $this->only(['id','pid','name','card','phone'])
            ->append('pid','checkStep:1')
            ;
    }


    public function sceneIndex_match()
    {
        $this->only(['pid','name','card','phone'])
            ->append('pid','checkStep:3')
            ;
    }

    //面谈
    public function sceneAdmin_face()
    {
        $this->only(['id','face_uid'])
        ;
    }


    //流程步骤验证
    public function checkStep($value,$rule,$data=[])
    {
        //获取项目信息
        $model = new \app\common\model\Product();
        $model = $model->get($value);
        if(empty($model)){
            return '项目信息异常';
        }

        if($rule==1){

        }else{
            //步骤一
            if(empty($data['money'])){
                return '请输入贷款金额';

            }elseif ($model['money_start'] > $data['money']){
                return '贷款金额不得低于项目最低金额'.$model['money_start'].\app\common\model\Product::$money_unit[$model['money_unit']];

            }elseif (!$model['money_end'] && $model['money_start'] != $data['money']){
                return '贷款金额只能为'.$model['money_start'].\app\common\model\Product::$money_unit[$model['money_unit']];

            }elseif ($model['money_end'] && $model['money_end'] < $data['money']){
                return '贷款金额不得高于项目最高金额'.$model['money_end'].\app\common\model\Product::$money_unit[$model['money_unit']];

            }elseif(empty($data['auth_time'])){
                return '请输入贷款期限';

            }elseif ($model['auth_time_start'] > $data['auth_time']){
                return '贷款期限不得低于项目最低期限:'.$model['auth_time_start'].\app\common\model\Product::$auth_unit[$model['auth_unit']];

            }elseif (!$model['auth_time_end'] && $model['auth_time_start'] != $data['auth_time']){
                return '贷款期限只能为:'.$model['auth_time_start'].\app\common\model\Product::$auth_unit[$model['auth_unit']];

            }elseif ($model['auth_time_end'] && $model['auth_time_end'] < $data['auth_time']){
                return '贷款期限不得高于项目最高期限:'.$model['auth_time_end'].\app\common\model\Product::$auth_unit[$model['auth_unit']];

            }
        }

        return true;
    }
}