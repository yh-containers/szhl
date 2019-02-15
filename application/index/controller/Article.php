<?php
namespace app\index\controller;

class Article extends Common
{
    //列表
    public function index()
    {
        return view('index',[

        ]);
    }


    /*
     * 产品列表
     * */
    public function showList()
    {
        $model = new \app\common\model\Article();
        $arr=[];
        $list = $model->paginate()->each(function(&$item, $key)use(&$arr){
            $item= [
                'id' => $item['id'],
                'title' => $item['title'],
                'img' => $item['img'],
                'send_time' => $item['send_time'],
                'view' => $item['view'],
            ];
            return $item;
        });

        return ['code'=>1,'msg'=>'获取成功','data'=>$list];
    }


    /*
     * 详情
     * */
    public function detail()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Article();
        $model->where('id',$id)->setInc('view');
        $model = $model->get($id);
        return view('detail',[
            'model' => $model,
        ]);
    }
}