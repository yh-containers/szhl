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

/**
 * curl 函数
 * @param string $url 请求的地址
 * @param string $type POST/GET/post/get
 * @param array $data 要传输的数据
 * @param string $err_msg 可选的错误信息（引用传递）
 * @param int $timeout 超时时间
 * @param array 证书信息
 * @author liw@alltosun.com
 */
function go_curl($url, $type='GET', $data = false, &$err_msg = null, $timeout = 20, $cert_info = array())
{
    $type = strtoupper($type);
    if ($type == 'GET' && is_array($data)) {
        $data = http_build_query($data);
    }
    $option = array();
    if ( $type == 'POST' ) {
        $option[CURLOPT_POST] = 1;
    }
    if ($data) {
        if ($type == 'POST') {
            $option[CURLOPT_POSTFIELDS] = $data;
        } elseif ($type == 'GET') {
            $url = strpos($url, '?') !== false ? $url.'&'.$data :  $url.'?'.$data;
        }
    }
    $option[CURLOPT_URL]            = $url;
    $option[CURLOPT_FOLLOWLOCATION] = TRUE;
    $option[CURLOPT_MAXREDIRS]      = 4;
    $option[CURLOPT_RETURNTRANSFER] = TRUE;
    $option[CURLOPT_TIMEOUT]        = $timeout;
    //设置证书信息
    if(!empty($cert_info) && !empty($cert_info['cert_file'])) {
        $option[CURLOPT_SSLCERT]       = $cert_info['cert_file'];
        $option[CURLOPT_SSLCERTPASSWD] = $cert_info['cert_pass'];
        $option[CURLOPT_SSLCERTTYPE]   = $cert_info['cert_type'];
    }
    //设置CA
    if(!empty($cert_info['ca_file'])) {
        // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
        $option[CURLOPT_SSL_VERIFYPEER] = 1;
        $option[CURLOPT_CAINFO] = $cert_info['ca_file'];
    } else {
        // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
        $option[CURLOPT_SSL_VERIFYPEER] = 0;
    }
    $ch = curl_init();
    curl_setopt_array($ch, $option);
    $response = curl_exec($ch);
    $curl_no  = curl_errno($ch);
    $curl_err = curl_error($ch);
    curl_close($ch);
    // error_log
    if($curl_no > 0) {
        if($err_msg !== null) {
            $err_msg = '('.$curl_no.')'.$curl_err;
        }
    }
    return $response;
}