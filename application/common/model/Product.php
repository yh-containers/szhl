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


    //设置标签属性
    public function setLabelsAttr($value,$data)
    {
        return $value?implode(',',$value):'';
    }
    //设置标签属性
    public function getLabelsAttr($value,$data)
    {
        return $value?explode(',',$value):null;
    }

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



    /*
     * 额度单位
     * @param $transform bool|int 转化比例 元
     * */
    public static function moneyUnit($transform=false)
    {
        if(is_bool($transform)){
            return ['unit'=>self::$money_unit,'default'=>2,'hide'=>[0,1]];
        }
        $transform_per = [1,1000,10000];
        return isset($transform_per[$transform])?$transform_per[$transform]:null;

    }

    //授权期限单位
    public static function authUnit($transform=false)
    {
        if(is_bool($transform)){
            return ['unit'=>self::$auth_unit,'default'=>1,'hide'=>[0,2]];

        }
        //只能按月
        return 0.01;
    }

    //授权期限单位
    public static function perUnit()
    {
        return ['unit'=>self::$per_unit,'default'=>1,'hide'=>[0,2]];
    }

    public static function init()
    {
        //新增完成后触发
        self::event('after_insert', function ($model) {
            $model->linkLogs()->save(['intro'=>'创建项目','opt_uid'=>!empty($model['opt_uid'])?$model['opt_uid']:0]);
            //创建提示消息
            UserMessage::recordMsg(2,$model['name'],'',0,0,['id'=>$model['id']]);
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
//            elseif (isset($model['proxy_id']) && $model['proxy_id']!=$proxy_id){
//                //项目委派
//                $model->linkLogs()
//                    ->save([
//                        'proxy_id' => $model['proxy_id'],
//                        'intro'=>'项目委派给代理商:用户名'.$model['opt_proxy_name'].PHP_EOL.'手机号:'.$model['opt_proxy_account'],
//                        'opt_uid'=>isset($model['opt_manage_id'])?$model['opt_manage_id']:0
//                    ]);
//
//            }
            elseif(!empty($model['opt_uid'])){
                $model->linkLogs()->save([
                    'proxy_id' => $model['proxy_id'],
                    'intro'=>'更新项目',
                    'opt_uid'=>$model['opt_uid']
                ]);

            }
        });
    }

    /*
     * 获取项目类型
     * */
    public static function getTypeInfo($type=1)
    {
        $type_info = [];
        foreach (self::$type_label as $vo){
            if($vo['type']==$type){
                $type_info=$vo;
                break;
            }
        }
        return $type_info;
    }
    /*
     * 项目申请流程
     * @param $step int 项目步骤
     * @param $flow_id int 流程
     * return array 数据 ，提示信息
     * */
    public function reqFlow($step=0,$flow_id=0)
    {
        $model = new ProductReq();
        $data = $model->getStepInfo($step,$this->getData('id'),$flow_id);
        //提示信息
        $tip = '';
        if($step==0){
            $tip = '资料仅用于产品方客户经理进准入和额度评估。';
        }
        return [
            $data,
            $tip
        ];
    }

    /*
     * 产品委托流程
     * */
    public function delegate($id,$proxy,$opt_id)
    {
        empty($id) && abort(4000,'产品信息异常');
        empty($proxy) && abort(4000,'代理商信息异常');
        //产品信息
        $model = $this->get($id);
        empty($model) && abort(4000,'产品信息异常');

        $model_proxy_pro = new ProxyProduct();
        //已存在的代理商
        $exist_proxy = $model_proxy_pro->where('pid',$id)->column('proxy_id');
        $opt_proxy = [];
        //排除已存在的代理
        if(is_array($proxy)){
            $opt_proxy = array_diff($proxy,$exist_proxy);
        }elseif(!in_array($proxy,$exist_proxy)){
            $opt_proxy = [$proxy];
        }
        $opt_proxy = array_unique($opt_proxy);

        $opt_data = [];
        foreach($opt_proxy as $vo){
            $opt_data[] = [
                'proxy_id'  =>  $vo,
                'pid'       =>  $id,
            ];
        }
        //保存所有数据
        $model_proxy_pro->saveAll($opt_data);

        //项目委派-增加日志
        $model->linkLogs()
            ->save([
                'proxy_id' => $model['proxy_id'],
                'intro'=>'项目委派给代理商',
                'opt_uid'=>$opt_id
            ]);

        return true;
    }


    //关联日志
    public function linkLogs()
    {
        return $this->hasMany('ProductLogs','pid')->order('id','desc');
    }

    //关联代理商--一条数据
    public function linkProxy()
    {
        return $this->hasOne('ProxyProduct','pid');
    }

    //关联代理商--一条数据
    public function linkChoose()
    {
        return $this->hasMany('ProductChoose','pid');
    }
}