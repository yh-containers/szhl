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
     * 产品列表
     * */
    public function showList()
    {
        $model = new \app\common\model\Product();
        $arr=[];
        $list = $model->paginate()->each(function(&$item, $key)use(&$arr){
            $item= [
                'id' => $item['id'],
                'name' => $item['name'],
                'per' => $item['per'],
                'per_unit' => \app\common\model\Product::$per_unit[$item['per_unit']],
                'auth_time_start'=> $item['auth_time_start'],
                'auth_time_end'=> $item['auth_time_end'],
                'auth_unit'=> \app\common\model\Product::$auth_unit[$item['auth_unit']],
                'money_start'=> $item['money_start'],
                'money_end'=> $item['money_end'],
                'money_unit'=> \app\common\model\Product::$money_unit[$item['money_unit']],
                'intro'=> $item['intro'],
                'is_hot'=> $item['is_hot'],
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