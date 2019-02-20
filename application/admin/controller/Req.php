<?php
namespace app\admin\controller;

class Req extends Common
{
    public function index()
    {
        $model = new \app\common\model\ProductReq();
        $list = $model->paginate();
        return view('index',[
            'list'=>$list,
            'money_unit' => \app\common\model\Product::$money_unit,
            'auth_unit' => \app\common\model\Product::$auth_unit,
        ]);
    }

    //详情
    public function detail()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\ProductReq();
        $model = $model->with(['linkLogs','linkPlan'])->get($id);
        return view('detail',[
            'model' =>$model,
            'money_unit' => \app\common\model\Product::$money_unit,
            'auth_unit' => \app\common\model\Product::$auth_unit,
            'per_unit' => \app\common\model\Product::$per_unit,
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
