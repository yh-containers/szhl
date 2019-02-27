<?php
namespace app\admin\controller;

class Req extends Common
{
    public function index()
    {
        $where=[];
        //删选状态
        $status = $this->request->param('status',0,'intval');
        if($status==1){
            //未审核
            $where[] = ['auth_status','=',0];
        }elseif ($status==2){
            //未面谈
            $where[] = ['auth_status','gt',0];
            $where[] = ['face_status','=',0];
        }elseif ($status==3){
            //审核通过
            $where[] = ['auth_status','=',1];
        }elseif ($status==4){
            //审核拒绝
            $where[] = ['auth_status','=',2];
        }elseif ($status==5){
            //已放款
            $where[] =['send_award_status','=',1];

        }elseif ($status==6){
            //已指派
            $where[] = ['p_auth_mid','gt',0];
        }elseif ($status==7){
            //未指派
            $where[] =['p_auth_mid','eq',0];

        }
        //绑定代理商用户
        $this->proxy_id && $where[] =['proxy_id','=',$this->proxy_id];
        //获取当前登录者身份
        $model_manage = new \app\common\model\Manage();
        $manage_info = $model_manage->get($this->user_id);
        if(!in_array($manage_info['rid'],\app\admin\middleware\CheckAuth::$ignore_role_id)){
            //按指派查询
            $where[]=['p_auth_mid','=',$this->user_id];
        }

        $model = new \app\common\model\ProductReq();
        $list = $model->where($where)->paginate();

        //指派员工

        $manage_list = $model_manage->where('proxy_id',$this->proxy_id)->select();


        return view('index',[
            'list'=>$list,
            'money_unit' => \app\common\model\Product::$money_unit,
            'auth_unit' => \app\common\model\Product::$auth_unit,
            'manage_list' => $manage_list,
            'status' => $status,
        ]);
    }

    //详情
    public function detail()
    {
        $where=[];
        //绑定代理商用户
        $this->proxy_id && $where[] =['proxy_id','=',$this->proxy_id];
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\ProductReq();
        $model = $model->where($where)->with(['linkLogs','linkPlan'])->get($id);

        //指派员工
        $model_manage = new \app\common\model\Manage();
        $manage_list = $model_manage->where('proxy_id',$this->proxy_id)->select();

        return view('detail',[
            'model' =>$model,
            'money_unit' => \app\common\model\Product::$money_unit,
            'auth_unit' => \app\common\model\Product::$auth_unit,
            'per_unit' => \app\common\model\Product::$per_unit,
            'manage_list' => $manage_list,
        ]);
    }

    //面谈动作
    public function faceAction()
    {
        $php_input = $this->request->param();
        $validate = new \app\common\validate\ProductReq();
        $validate->scene(self::SCENE.'_face');
        $model = new \app\common\model\ProductReq();
        $php_input['face_uid'] = $this->user_id;    //操作用户
        return $model->actionAdd($php_input,$validate);
    }


    //处理审核
    public function handleAuth()
    {
        $id = $this->request->param('id',0,'intval');
        $auth_status = $this->request->param('auth_status');
        $auth_content = $this->request->param('auth_content');
        $model = new \app\common\model\ProductReq();

        try{
            $state = $model->authReq($id,$this->user_id,$auth_status,$auth_content);
            return ['code'=>is_bool($state)?1:0,'msg'=>is_bool($state)?'操作成功':$state];
        }catch (\Exception $e){
            return ['code'=>0, 'msg'=>'操作异常:'.$e->getMessage()];
        }
    }

    //发放奖金
    public function sendAward()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\ProductReq();
        try{
            $state = $model->sendAward($id,$this->user_id);
            return ['code'=>is_bool($state)?1:0,'msg'=>is_bool($state)?'操作成功':$state];
        }catch (\Exception $e){
            return ['code'=>0, 'msg'=>'操作异常:'.$e->getMessage()];
        }

    }

    //指定用户
    public function pointManager()
    {
        $id = $this->request->param('id',0,'intval');
        $p_auth_mid = $this->request->param('p_auth_mid',0,'intval');
        if(empty($id)) return ['code'=>0,'msg'=>'操作对象异常!'];
        if(empty($p_auth_mid)) return ['code'=>0,'msg'=>'请选择指派的业务员!'];

        $model = new \app\common\model\ProductReq();
        $php_input = [
            'id' => $id,
            'p_auth_mid' => $p_auth_mid,
            'p_auth_time' => time()
        ];
        return $model->actionAdd($php_input);
    }

    //还款计划操作
    public function handlePlanAction()
    {
        $php_input = $this->request->param();
        $validate = new \app\common\validate\UserProductPlan();
        $validate->scene(self::SCENE.'_handle_plan');
        $model = new \app\common\model\UserProductPlan();
        $php_input['status'] = 1;    //已还款
        $php_input['opt_uid'] = $this->user_id;    //操作用户
        return $model->actionAdd($php_input,$validate);
    }

}
