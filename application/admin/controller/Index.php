<?php
namespace app\admin\controller;

class Index extends Common
{
    public function index()
    {
        $model = model('Node');
        //区分代理还是平台
        $prefix = $this->proxy_id?'proxy_':'platform_';
        //关联查询
        $with = $this->proxy_id?'linkNodeProxy':'linkNode';

        $where = [
            [$prefix.'pid','=',0],
            [$prefix.'status','=',1],
        ];
        $order_field = $prefix.'sort';

        $node = $model->with([$with=>function($query)use($prefix, $order_field){
            return $query->where($prefix.'status',1)->order($order_field, 'asc');
        }])->where($where)->order($order_field, 'asc')->select();
        return view('index',[
            'node'=>$node
        ]);
    }

    public function welcome()
    {
        return view('welcome');
    }
}
