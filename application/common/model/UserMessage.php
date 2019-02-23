<?php
namespace app\common\model;

class UserMessage extends Base
{
    protected $name='user_message';

    protected $json = ['r_cond'];

    const MSG_TYPE = [
        ['name'=>'最新资讯','r_type'=>[
            'article/detail',
        ]],
        ['name'=>'系统消息','r_type'=>[
            'progress/detail',
        ]],
        ['name'=>'产品消息','r_type'=>[
            'product/detail',
        ]],
        ['name'=>'滚动消息','r_type'=>[
        ]],
    ];
    public static function recordMsg($type,$title,$content='',$uid=0,$r_type=0,$r_cond=[],$link_id=0)
    {
        $model = new self();
        $model->save([
            'uid' => $uid,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'r_type' => $r_type,
            'r_cond' => $r_cond,
            'link_id' => $link_id,
        ]);
    }

    public function linkRead()
    {
        return $this->hasOne('UserMessageRead','mid');
    }

    //消息关联文章
    public function linkArticle()
    {
        return $this->belongsTo('Article','link_id');
    }
}