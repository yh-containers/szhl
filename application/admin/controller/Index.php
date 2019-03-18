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
        $with_fields = $this->proxy_id?'link_node_proxy':'link_node';

        $where = [
            [$prefix.'pid','=',0],
            [$prefix.'status','=',1],
        ];
        $order_field = $prefix.'sort';

        $node = $model->with([$with=>function($query)use($prefix, $order_field){
            return $query->where($prefix.'status',1)->order($order_field, 'asc');
        }])->where($where)->order($order_field, 'asc')->select();

        return view('index',[
            'node'=>$node,
            'with_fields'=>$with_fields,
        ]);
    }

    public function welcome()
    {
        $today_time = strtotime(date("Y-m-d"));
        $where = [];
        //绑定代理商用户
        $this->proxy_id && $where[] =['proxy_id','=',$this->proxy_id];
        //注册会员
        $today_reg_users = (new \app\common\model\Users())->where($where)->where('create_time','egt',$today_time)->count();
        //今日申请数量
        $today_req_num = (new \app\common\model\ProductReq())->where($where)->where('create_time','egt',$today_time)->count();
        //已完成数量
        $complete_num = (new \app\common\model\ProductReq())->where($where)->where('status','gt',1)->count();
        //未完成数量
        $uncompleted_num = (new \app\common\model\ProductReq())->where($where)->where('status','eq',1)->count();

        return view('welcome',[
            'today_reg_users' => $today_reg_users,
            'today_req_num' => $today_req_num,
            'complete_num' => $complete_num,
            'uncompleted_num' => $uncompleted_num,
        ]);
    }
}
