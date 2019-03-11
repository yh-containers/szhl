<?php
namespace app\common\model;


class ProductSpuCol extends Base
{
    protected $name='product_spu_col';

    public static $fields_type = ['可选值','文本输入'];

    protected $json = ['content'];

    public function linkSpu()
    {
        return $this->belongsTo('ProductSpu','sid');
    }
}