<?php
namespace app\common\model;



class ProxyReq extends Base
{
    protected $name='proxy_req';
    public static $fields_status=['异常','申请','已处理'];

    //处理申请
    public function handleReq($id,$data)
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

        return true;
    }
}