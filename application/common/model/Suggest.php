<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Suggest extends Base
{
    use SoftDelete;
    protected $name='suggest';

    //关联
    public function linkUser()
    {
        return $this->hasOne('Users','id','uid');
    }
}