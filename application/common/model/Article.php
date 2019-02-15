<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Article extends Base
{
    use SoftDelete;
    protected $name='article';
    public static $fields_status_article=['异常','已发布','取消发布','删除'];

    public function articleDelete($id)
    {

        $model = $this->where('id',$id)->find($id);
        $model->delete();
        if($model){

        }else{

        }
        dump($model);
//        $status = $this->where('id',$id)->delete();

    }

}