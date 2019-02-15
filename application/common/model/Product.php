<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Product extends Base
{
    use SoftDelete;
    protected $name='product';

    public static $fields_status=['异常','正常','禁用','冻结'];
    //利息单位
    public static $per_unit = ['日','月','年'];
    //额度单位
    public static $money_unit = ['元','千元','万元'];
    //授权期限单位
    public static $auth_unit = ['天','月','年'];

    //产品菜单
    public static $type_label = [
        ['name'=>'信用贷款','nav'=>'1','type'=>1],
        ['name'=>'房产抵押','nav'=>'2','type'=>2],
        ['name'=>'车辆抵押','nav'=>'3','type'=>3],
        ['name'=>'急速贷款','nav'=>'4','type'=>4],
    ];
    //项目条件-设置
    public function setConditionAttr($value)
    {
        $value=array_values(array_filter($value));
        return json_encode($value);
    }
    //项目条件-获取
    public function getConditionAttr($value)
    {
        $data = json_decode($value,true);
        if(empty($data)){
            return [];
        }
        $arr = [];
        foreach($data as $vo){
            $index = mb_strpos($vo,"\n",0,'utf8');
            $arr[] =[
                'title' => mb_substr($vo,0,$index,'utf8'),
                'desc'  => mb_substr($vo,$index+1,-1,'utf8'),
            ];
        }
        return $arr;
    }



    //额度单位
    public static function moneyUnit()
    {
        return ['unit'=>self::$money_unit,'default'=>2];
    }

    //授权期限单位
    public static function authUnit()
    {
        return ['unit'=>self::$auth_unit,'default'=>1];
    }

    //授权期限单位
    public static function perUnit()
    {
        return ['unit'=>self::$per_unit,'default'=>1];
    }

    public static function init()
    {
        //新增完成后触发
        self::event('after_insert', function ($model) {
            $model->linkLogs()->save(['intro'=>'创建项目','opt_uid'=>app('auth_user_id')]);
        });
        //新增完成后触发
        self::event('after_update', function ($model) {
            $status = $model->getOrigin('status');
            if(isset($model['status']) && $model['status']!=$status){
                $intro_status = ['','启动项目','禁用项目','冻结项目'];
                $model->linkLogs()->save(['intro'=>'更新项目:'.$intro_status[$model['status']],'opt_uid'=>app('auth_user_id')]);
            }else{
                $model->linkLogs()->save(['intro'=>'更新项目','opt_uid'=>app('auth_user_id')]);
            }
        });
    }

    //关联日志
    public function linkLogs()
    {
        return $this->hasMany('ProductLogs','pid')->order('id','desc');
    }
}