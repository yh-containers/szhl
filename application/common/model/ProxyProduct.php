<?php
namespace app\common\model;


class ProxyProduct extends Base
{
    protected $name='proxy_product';

    protected $insert = ['status'=>1];

    public static $fields_status = ['异常','正常','关闭'];

    public static function init()
    {
        //新增完成后触发
        self::event('after_insert', function ($model) {
            $model->linkLogs()->save(['intro'=>'获得项目代理权限','proxy_id'=>$model['proxy_id']]);
        });
        //新增完成后触发
        self::event('after_update', function ($model) {
            $status = $model->getOrigin('status');
            $proxy_id = $model->getOrigin('proxy_id');
            if(isset($model['status']) && $model['status']!=$status){
                $intro_status = ['','启动项目','禁用项目','冻结项目'];
                $model->linkLogs()->save([
                    'proxy_id' => $model['proxy_id'],
                    'intro'=>'更新项目:'.$intro_status[$model['status']],
                    'opt_uid'=>!empty($model['opt_uid'])?$model['opt_uid']:0
                ]);

            }
        });
    }

    //关联产品
    public function linkProduct()
    {
        return $this->belongsTo('Product','pid');
    }

    //关联日志
    public function linkLogs()
    {
        return $this->hasMany('ProductLogs','pid','pid')->order('id','desc');
    }

    //关联代理商
    public function linkManage()
    {
        return $this->belongsTo('Manage','proxy_id');
    }
}