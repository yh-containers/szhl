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
        $model = $model->with(['linkLogs'])->get($id);
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


    //调整用户信息
    public function modifyInfo()
    {
        $id = $this->request->param('id',0,'intval');
        $auth_status = $this->request->param('auth_status');
        $auth_content = $this->request->param('auth_content');
        $model = new \app\common\model\ProductReq();
//        $php_input['auth_uid'] = $this->user_id;    //操作用户
        $state = $model->authReq($id,$this->user_id,$auth_status,$auth_content);
        return ['code'=>is_bool($state)?1:0,'msg'=>is_bool($state)?'操作成功':$state];
    }

}
