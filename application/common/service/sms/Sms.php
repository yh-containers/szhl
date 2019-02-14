<?php
namespace app\common\service\sms;

class Sms
{

    const USER_ID = '58319';
    const ACCOUNT = '18123896256';
    const PASSWORD = 'SZhulian2030';

    public static function send($content,$phone)
    {
        $url='http://dx.qxtsms.cn/sms.aspx?action=send'.
        '&userid='.self::USER_ID.
        '&account='.self::ACCOUNT.
        '&password='.self::PASSWORD.
        '&mobile='.$phone.
        '&content='.$content.
        '&sendTime=&checkcontent=1';
        echo ($url);
        return go_curl($url);
    }
}