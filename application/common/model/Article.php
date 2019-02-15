<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Article extends Base
{
    use SoftDelete;
    protected $name='article';
    public static $fields_status_article=['异常','已发布','取消发布','删除'];
    public function setSort($id,$sort)
    {
        $data['sort'] = $sort;
        $where[] = ['id','=',$id];
        $this->where($where)->update($data);
        return ['code'=>1,'msg'=>'更新成功','url'=>'article'];
    }

}