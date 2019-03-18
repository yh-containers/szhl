<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class Role extends Base
{
    use SoftDelete;
    protected $name='sys_role';

    public function setNodeAttr($value,$data)
    {
        return $value?implode(',',$value):'';
    }

    public function getNodeAttr($value,$data)
    {
        return $value?strtolower($value):'';
    }

    public function linkChild()
    {
        return $this->hasMany('Role','pid');
    }
}