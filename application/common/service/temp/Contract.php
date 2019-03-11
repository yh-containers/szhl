<?php
namespace app\common\service\temp;

use app\common\model\Users;

class Contract
{

    /*
     * 所有魔术变量
     * */
    public static function getAllVar()
    {
        return [
//            ['name'=>'公司名','var'=>'{COMPANY_NAME}'],
            ['name'=>'用户名','var'=>'{USER_NAME}'],
            ['name'=>'用户手机号','var'=>'{PHONE}'],
            ['name'=>'日期(年月日 小时分秒)','var'=>'{DATE_TIME}'],
            ['name'=>'日期(年月日)','var'=>'{DATE}'],
            ['name'=>'合同盖章','var'=>'{CHAPTER}'],
        ];
    }

    /*
     * 模版魔术变量替换
     * */
    public static function changeContent(Users $user_model)
    {
        $temp_var = [
            '{COMPANY_NAME}'    => '公司名',
            '{USER_NAME}'       => empty($user_model['name'])?'':$user_model['name'],
            '{PHONE}'           => empty($user_model['phone'])?'':$user_model['phone'],
            '{DATE_TIME}'       => date('Y-m-d H:i:s'),
            '{DATE}'            => date('Y-m-d'),
            '{CHAPTER}'         => '<div style="width:120px;height: 120px;"><img style="width: inherit;height: inherit;border-radius: 50%" src="static/images/zhang.png"/></div>',
        ];
        //模版内容
        $content = (new \app\common\model\Setting())->getContent('contract_temp');
        $change_content = str_replace(array_keys($temp_var),array_values($temp_var),$content,$replace_count);
        return $change_content;

    }
}