<?php
namespace app\common\model;

use think\Model;
use think\Validate;

class Base extends Model
{
    public static $fields_status= ['','正常','禁用'];
    protected $autoWriteTimestamp = true;
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
    /*
     * 数据保存/更新
     * */
    public function actionAdd($data,Validate $validate=null)
    {
        if ($validate && !$validate->check($data)) {
            return ['code'=>0,'msg'=>$validate->getError()];
        }
        $model = $this;
        $pk = $this->getPk();
        if(!empty($data[$pk])){  //编辑状态
            $model = $this->find($data[$pk]);
        }else{
            //清除主键影响
            unset($data[$pk]);
        }
        try{
            $model && $model->save($data);
            return ['code'=>1,'msg'=>'操作成功'];
        }catch (\Exception $e) {
            return ['code'=>0,'msg'=>'操作异常'.$e->getMessage()];
        }


    }

    /*
     * 数据删除
     * */
    public function actionDel($id,$proxy_id=false)
    {
        try{
            $where[] =[$this->getPk(),'=',$id];
            $proxy_id!==false && $where[] =['proxy_id','=',$proxy_id];
            $model = $this->where($where)->find($id);
            $model && $model->delete();
            return ['code'=>1,'msg'=>'删除成功'];
        }catch (\Exception $e) {
            return ['code'=>0,'msg'=>'删除异常'.$e->getMessage()];
        }

    }

    //调整用户数据
//    public function modifyInfo($id,$data)
//    {
//
//        return $this->save($data,[$this->getPk()=>$id]);
//    }


}