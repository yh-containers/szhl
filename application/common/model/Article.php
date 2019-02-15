<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Article extends Base
{
    use SoftDelete;
    protected $name='article';
    public static $fields_status_article=['异常','已发布','取消发布','删除'];


}