<?php
namespace app\index\controller;

class Article extends Common
{
    //列表
    public function index()
    {
        $model = new \app\common\model\Article();
        return view('index',[
            'model' => $model,
        ]);
    }
    /*
     * 详情
     * */
    public function detail()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Product();
        $model = $model->get($id);
        return view('detail',[
            'model' => $model,
        ]);
    }
}