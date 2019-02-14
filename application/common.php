<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/*
 * 数组按某个字段分组
 * @param  $data array
 * @param $field string
 * return array
 * */
function arr_field_group(array $data,$field)
{
    $arr = [];
    foreach ($data as $vo) {
        if(array_key_exists($vo[$field],$arr)) {
            $arr[$vo[$field]]['data'][] =$vo;
        }else{
            $arr[$vo[$field]] =[
                'field' => $vo[$field],
                'data'  =>  [$vo]
            ];
        }
    }
    return array_values($arr);
}

