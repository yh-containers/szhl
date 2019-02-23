<?php
namespace app\index\controller;

class Message extends Common
{
    protected $need_login=true;
    //信息
    public function index()
    {
        $model = new \app\common\model\UserMessage();
        $data = [];
        $model_link = $model->withJoin(['linkRead'],'left')->order('user_message.id','desc')->whereIn('user_message.uid',[0,$this->user_id]);
        foreach($model::MSG_TYPE as $key=>$vo){
            //未读消息
            $num = $model_link->whereNull('linkRead.id')->where('type',$key)->count();
            //去除查询条件
            $model_link->removeWhereField('type');
            $model_link->removeWhereField('linkRead.id');
            $intro = $model_link->where('type',$key)->value('title');
            //去除查询条件
            $model_link->removeWhereField('type');

            $data[] = [
                'num' => $num,
                'intro' => $intro?$intro:'暂无消息',
            ];
        }
        return view('index',[
            'data'=> $data
        ]);
    }
    //信息
    public function msgList()
    {
        $type = $this->request->param('type',0,'intval');
        return view('msgList',[
            'type'=>$type,
            'msg_title' => \app\common\model\UserMessage::MSG_TYPE[$type]['name']
        ]);
    }
    //信息
    public function msgArticleList()
    {
        return view('msgArticleList',[
            'type'=>0,
            'msg_title' => \app\common\model\UserMessage::MSG_TYPE[0]['name']
        ]);
    }

    //查看数据
    public function showList()
    {
        $type = $this->request->param('type',1,'intval');
        //产品labels属性
        $model = new \app\common\model\UserMessage();
        $where[] = [
            ['type','=',$type],
        ];
        if($type===0){
            $model =  $model->with('linkArticle');
        }
        //当前用户
        $user_id = $this->user_id;
        $list = $model
            ->withJoin(['linkRead'],'left')
            ->whereIn('user_message.uid',[0,$user_id])
            ->where($where)
            ->order('id','desc')
            ->paginate()
            ->each(function(&$item, $key)use($user_id){
                $item_obj = $item;

                $msg_type = \app\common\model\UserMessage::MSG_TYPE;
                $msg_type = isset($msg_type[$item['type']])?$msg_type[$item['type']]:[];
                $uri = isset($msg_type)?$msg_type['r_type'][$item['r_type']]:'';
                if($item['type']===0){
                    //文章消息
                    $item = [
                        'date'  =>  $item['create_time'],
                        'img'   =>  $item['link_article']['img'],
                        'title' =>  $item['title'],
                        'author'=>  $item['link_article']['author'],
                        'url'   =>  url($uri,$item['r_cond']?$item['r_cond']:[],false,true),
                    ];
                }else{
                    //
                    $item= [
                        'date' => $item['create_time'],
                        'title' => $item['title'],
                        'content' => $item['content'],
                    ];
                }

                if(empty($item_obj['link_read'])){
                    //修改消息为已阅读状态
                    $item_obj->linkRead()->save([
                        'uid' => $user_id,
                    ]);
                }


                return $item;
        });


        return ['code'=>1,'msg'=>'获取成功','data'=>$list];
    }

}