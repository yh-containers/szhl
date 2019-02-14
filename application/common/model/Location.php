<?php
namespace app\common\model;


class Location extends Base
{
    protected $name='sys_location';

    /*
     * 读取缓存内容
     * */
    public function getContent($type)
    {
//        return $this->where('area_type','in',"0,1,2")->select();
    }
    //关联
    public function linkLocation()
    {
        return $this->hasMany('Location','parent_id','area_id');
    }

    /*
     * 读取缓存内容
     * */
    public function setContent($is_check,$area_id)
    {
        $data['is_hot'] = $is_check;
        $where[] = ['area_id','=',$area_id];
        return $this->where($where)->update($data);
    }

}