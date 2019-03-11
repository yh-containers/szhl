<?php
namespace app\index\controller;

class Proxy extends Common
{
    protected $need_login = true;
    protected $ignore_login_action = 'req';
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

                list($status_color,$status_name,$status_intro) = $item->getStatusInfo();
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
        $uid = $this->request->param('uid',0,'intval');
        $model_users = new \app\common\model\Users();
        $where[] = ['fuid1','=',$uid?$uid:$this->user_id];
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
            'uid'=>$uid,
            'total_up_number' =>$total_up_number,
            'current_month_up_number' =>$current_month_up_number,
            'current_day_up_number' =>$current_day_up_number,
            'req_success_number' =>$req_success_number,
        ]);
    }

    //我推荐的用户数据
    public function showClientList()
    {
        $uid = $this->request->param('uid',0,'intval');
        $model = new \app\common\model\Users();
        $where[] = ['fuid1','=',$uid?$uid:$this->user_id];
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

    //代理申请
    public function req()
    {
        if($this->request->isAjax()){
            $model = new \app\common\model\ProxyReq();
            $php_input = $this->request->param();
            $php_input['uid']= $this->user_id;
            $validate = new \app\common\validate\ProxyReq();
            $result=$model->actionAdd($php_input,$validate);
            return ['code'=>$result['code'],'msg'=>$result['code']?'申请成功,请耐心等待审核':$result['msg'],'url'=>url('Index/index')];
        }
        return view('req',[

        ]);
    }
}