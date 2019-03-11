<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class ProductSpu extends Base
{
    use SoftDelete;
    protected $name='product_spu';


    public function linkCol()
    {
        return $this->hasMany('ProductSpuCol','sid');
    }

}