<?php
namespace app\index\controller;

class Product extends Common
{

    public function index()
    {

        //产品labels
        $model_label = new \app\common\model\Label();
        $label_list = $model_label->select();
        return view('index',[
            'label_list' => $label_list,
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

    /*
     * 产品搜索
     * */
    public function search()
    {
        $keyword = $this->request->param('keyword','','trim');
        return view('search',[
            'keyword'=>$keyword,
        ]);
    }
}