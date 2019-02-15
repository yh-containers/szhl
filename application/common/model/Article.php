<?php
namespace app\common\model;


use think\model\concern\SoftDelete;

class Article extends Base
{
    use SoftDelete;
    protected $name='article';

}