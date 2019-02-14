<?php
namespace app\index\controller;

class Product extends Common
{
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