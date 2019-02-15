<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Suggest extends Base
{
    use SoftDelete;
    protected $name='suggest';


}