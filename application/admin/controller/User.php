<?php
namespace app\admin\controller;

class User extends Common
{
    public function index()
    {
        $model = new \app\common\model\Users();
        $list = $model->paginate();
        return view('index',[
            'list'=>$list,
        ]);

    }

    public function userDetail()
    {
        $id = $this->request->param('id',0);
        $model = new \app\common\model\Users();
        $model = $model->get($id);

        return view('userDetail',[
            'model' => $model
        ]);
    }

    //调整用户信息
    public function modifyInfo()
    {
        $php_input = $this->request->param();
        $model = new \app\common\model\Users();
        $state = $model->actionAdd($php_input);
        return ['code'=>(int)$state,'msg'=>$state?'修改成功':'修改异常'];
    }
}
