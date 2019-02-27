<?php
namespace app\index\controller;

class Progress extends Common
{
    protected $need_login = true;
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
        if($status==1){
            //审批中
            $where[] = ['status','=',1];
        }elseif($status==2){
            //面谈中
            $where[] =  ['status','=',1];
            $where[] =  ['face_status','=',0];
            $where[] =  ['auth_status','=',1];
        }elseif($status==3){
            //已放款
            $where[] =  ['send_award_status','=',1];
        }elseif($status==4){
            //已被拒
            $where[] =  ['auth_status','=',2];
        }
        $where[] =['uid','=',$this->user_id];
        $list = $model->where($where)->where('status','gt',0)->paginate()->each(function(&$item, $key)use(&$arr){

            list($status_color,$status_name,$status_intro) = $item->getStatusInfo();
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
                'face_status'    => $item['face_status'],
                'auth_status'    => $item['auth_status'],
                'status_name'=> $status_name,
                'status_color'=> $status_color,
                'status_intro'=> $status_intro,

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

    public function applyData()
    {
        return view('applyData',[

        ]);
    }
}