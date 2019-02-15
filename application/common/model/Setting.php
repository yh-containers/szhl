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
        //缓存数据
        return $this->cache($this->name.$type)->where($where)->value('content');
    }


    /*
     * 读取缓存内容
     * */
    public function setContent($type,$content)
    {
        //清空缓存
        cache($this->name.$type,null);
        $data['content'] = $content;
        $where[] = ['type','=',$type];
        return $this->where($where)->update($data);
    }

}