<?php
namespace app\index\controller;

class Progress extends Common
{
    //列表
    public function index()
    {
        $status = $this->request->param('status',null,'intval');

        return view('index',[
            'status' => $status,
        ]);
    }

    public function showList()
    {
        $status = $this->request->param('status',null,'intval');
        $model = new \app\common\model\ProductReq();
        $where = [];
        !is_null($status) && $where[] = ['status','=',$status];
        $list = $model->where($where)->paginate()->each(function(&$item, $key)use(&$arr){
            $item= [
                'id'        => $item['id'],
                'no'        => $item['no'],
                'name'      => $item['product_info']['name'],
                'auth_time' => $item['auth_time'],
                'auth_unit' => $item['auth_unit'],
                'auth_unit_name' => \app\common\model\Product::$auth_unit[$item['auth_unit']],
                'money'     => $item['money'],
                'money_unit'=>  $item['money_unit'],
                'money_unit_name'=>  \app\common\model\Product::$money_unit[$item['money_unit']],
                'status'    => $item['status'],
                'status_name'=> \app\common\model\ProductReq::$fields_status[$item['status']],

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
        $model = new \app\common\model\ProductReq();
        $model = $model->with(['linkLogs'])->get($id);
        return view('detail',[
            'model' => $model,
            'money_unit' => \app\common\model\Product::$money_unit,
            'auth_unit'  => \app\common\model\Product::$auth_unit,
        ]);
    }
}