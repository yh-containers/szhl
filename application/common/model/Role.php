<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class Role extends Base
{
    use SoftDelete;
    protected $name='sys_role';

}