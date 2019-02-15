<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class FlowImage extends Base
{
    use SoftDelete;
    protected $name='flow_image';

}