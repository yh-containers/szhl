<?php
namespace app\common\model;


class ProductTypeSpu extends Base
{
    protected $name='product_type_spu';


    //获取属性名
    public function linkSpu()
    {
        return $this->belongsTo('ProductSpuCol','sc_id');
    }
}