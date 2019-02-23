<?php
namespace app\index\controller;

class Proxy extends Common
{
    protected $need_login = true;
    //列表
    public function index()
    {
        return view('index',[

        ]);
    }

    //客户进度
    public function progress()
    {
        $order = $this->request->param('order','asc','trim');
        $order = $order=='asc'?'desc':'asc';
        $keyword = $this->request->param('keyword','','trim');

        return view('progress',[
            'keyword' => $keyword,
            'order' => $order,
        ]);
    }

    //客户进度--数据
    public function showProgressList()
    {
        $order = $this->request->param('order','asc','trim');
        $order = $order=='asc'?'asc':'desc';
        $where[] = ['linkUsers.fuid1','=',$this->user_id];
        $keyword = $this->request->param('keyword','','trim');
        !empty($keyword) && $where[] = ['product_req.name|product_req.phone','like','%'.$keyword.'%'];
        $model = new \app\common\model\ProductReq();
        $list = $model
            ->withJoin(['linkUsers'],'left')
            ->where($where)
            ->order('create_time',$order)
            ->paginate()->each(function(&$item,$index){
                $status_name = '审批中';
                $status_color = 2;
                if($item['auth_status']==1){
                    $status_name='已放款';
                    $status_color = 1;

                }elseif($item['auth_status']==2){
                    $status_name='已拒绝';
                    $status_color = 2;

                }elseif($item['face_status']==1){
                    $status_name='已面谈';
                    $status_color = 3;

                }

                $item=[
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'status_name'=> $status_name,
                    'status_color'=> $status_color,
                    'money' => $item['money'],
                    'create_time' => substr($item['create_time'],0,10),
                ];
                return $item;
            });
        return ['code'=>1,'msg'=>'获取成功','data'=>$list];
    }


    //我推荐的用户
    public function client()
    {
        $model_users = new \app\common\model\Users();
        $where[] = ['fuid1','=',$this->user_id];
        //推荐总人数
        $total_up_number = $model_users->where($where)->count();
        //本月推荐人数
        $current_month_up_number = $model_users->where('create_time','egt',strtotime(date('Y-m')))->where($where)->count();
        //今日推荐人数
        $current_day_up_number = $model_users->where('create_time','egt',strtotime(date('Y-m-d')))->where($where)->count();
        //成交数量
        $req_success_number = 0;
        $model_users->withCount(['linkReqList'=>function($query){
            return $query->where('status',2);
        }])->where($where)->select()->each(function($item,$index)use(&$req_success_number){
            $item['link_req_list_count'] && $req_success_number += $item['link_req_list_count'];
        });

        return view('client',[
            'total_up_number' =>$total_up_number,
            'current_month_up_number' =>$current_month_up_number,
            'current_day_up_number' =>$current_day_up_number,
            'req_success_number' =>$req_success_number,
        ]);
    }

    //我推荐的用户数据
    public function showClientList()
    {
        $model = new \app\common\model\Users();
        $where[] = ['fuid1','=',$this->user_id];
        $list = $model
            ->where($where)
            ->withSum(['linkReqList'=>function($query){
                return $query->where('status',2);
            }],'money')
            ->order('id','desc')
            ->paginate()->each(function(&$item,$index){
                $item=[
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'amount' => $item['link_req_list_sum']?$item['link_req_list_sum']:0.00,
                    'create_time' => substr($item['create_time'],0,10),
                ];
                return $item;
            });
        return ['code'=>1,'msg'=>'获取成功','data'=>$list];
    }
}