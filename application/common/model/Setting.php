<?php
namespace app\common\model;


class Setting extends Base
{
    protected $name='sys_setting';

    /*
     * 读取缓存内容
     * */
    public function getContent($type)
    {

        $where[] = ['type','=',$type];
        return $this->where($where)->value('content');
    }


    /*
     * 读取缓存内容
     * */
    public function setContent($type,$content)
    {
        $data['type'] = $type;
        $data['content'] = $content;
        $where[] = ['type','=',$type];
        return $this->where($where)->find()->save($data);
    }

}