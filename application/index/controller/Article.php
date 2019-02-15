<?php
namespace app\index\controller;

class Article extends Common
{
    //列表
    public function index()
    {
        if($this->request->isAjax()){
            $model = new \app\common\model\Article();
            $data = $model->paginate();
            return ['code'=>0,'msg'=>'获取成功','data'=>$data];
        }

        return view('index',[

        ]);
    }
    /*
     * 详情
     * */
    public function detail()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Article();
        $model = $model->get($id);
        return view('detail',[
            'model' => $model,
        ]);
    }
}