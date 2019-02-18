<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class ProductReq extends Base
{
    use SoftDelete;
    public static $fields_status=['创建请求','创建申请','已完成'];
    public static $fields_face_status=['未面谈','已面谈'];
    public static $fields_auth_status=['未审核','已审核','拒绝'];

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
    protected $insert = ['no'];
    protected $auto=['status'];
    protected $json = ['product_info'];
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
            $this->setAttr('product_info',$model);
            $this->setAttr('p_tid',$model['type']);//项目类型
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
    public function setAuthStatusAttr($value,$data)
    {
        if($value){
            $this->setAttr('auth_date_time',time());
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
        });
        //新增完成后触发
        self::event('after_update', function ($model) {
            $status = $model->getOrigin('status');
            $face_status = $model->getOrigin('face_status');
            $auth_status = $model->getOrigin('auth_status');
            //申请状态更改
            if(isset($model['status']) && $model['status']!=$status && $model['status']==1){
                $title = '提交申请成功';
                $intro = '申请额度'.$model['money'].Product::$money_unit[$model['money_unit']].',期限'.$model['auth_time'].Product::$auth_unit[$model['auth_unit']];
                $model->linkLogs()->saveAll([
                    ['title'=>$title,'intro'=>$intro],
                    ['title'=>'审批中','intro'=>'请耐心等待，工作人员将尽快与您取得联系'],
                ]);

            }elseif(isset($model['status']) && $model['status']!=$status && $model['status']==2){
                $title = '交易完成';
                $intro = '交易已结束，感谢您的使用';
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);

            }elseif(isset($model['status']) && $model['status']==$status){
                $title = '更新资料';
                $model->linkLogs()->save(['title'=>$title,'intro'=>'更新申请资料']);
            }

            //面谈
            if(isset($model['face_status']) &&  $model['face_status']!=$face_status && $model['face_status']==1){
                $title = '面谈完成';
                $intro = $model['face_content'];
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
            }
            //审核
            if(isset($model['auth_status']) &&  $model['auth_status']!=$auth_status && $model['auth_status']==1){
                $title = '审核被拒';
                $intro = $model['auth_content']?$model['auth_content']:'';
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
            }elseif(isset($model['auth_status']) &&  $model['auth_status']!=$auth_status && $model['auth_status']==2){
                $title = '恭喜您通过审核';
                $intro = $model['auth_content']?$model['auth_content']:'恭喜您通过审核';
                $model->linkLogs()->save(['title'=>$title,'intro'=>$intro]);
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


    //关联日志
    public function linkLogs()
    {
        return $this->hasMany('ProductReqLogs','req_id')->order('id','desc');
    }
    //关联项目
//    public function linkProduct()
//    {
//        return $this->belongsTo('Product','pid');
//    }
}