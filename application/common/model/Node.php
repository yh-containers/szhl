<?php
namespace app\common\model;

class Node extends Base
{
    protected $name='sys_node';
    //关闭时间
    protected $autoWriteTimestamp = false;

    //关联查询--平台
    public function linkNode()
    {
        return $this->hasMany('Node','platform_pid');
    }
    //关联查询--代理商
    public function linkNodeProxy()
    {
        return $this->hasMany('Node','proxy_pid');
    }
}