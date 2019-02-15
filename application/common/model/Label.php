<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Label extends Base
{
    use SoftDelete;
    protected $name='product_label';


}