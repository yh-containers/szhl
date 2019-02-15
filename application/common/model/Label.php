<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Label extends Base
{
    use SoftDelete;
    protected $name='product_label';

    public function setSort($id,$sort)
    {
        $data['sort'] = $sort;
        $where[] = ['id','=',$id];
        $this->where($where)->update($data);
        return ['code'=>1,'msg'=>'更新成功','url'=>'labels'];
    }

}