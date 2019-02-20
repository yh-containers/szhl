<?php
namespace app\admin\controller;

class Withdraw extends Common
{
    public function index()
    {
        $model = new \app\common\model\UserWithdraw();
        $list = $model->with(['linkUserInfo'])->paginate();
        return view('index',[
            'list'=>$list,
        ]);
    }

    /*
     * 处理用户提现
     * */
    public function handleAction()
    {
        $id = $this->request->param('id',0,'intval');
        $php_input = $this->request->param();
        try{
            $php_input['opt_uid'] = $this->user_id;
            $model = new \app\common\model\UserWithdraw();
            $model->handleDraw($id,$php_input);
            return ['code'=>1,'msg'=>'操作成功'];
        }catch (\Exception $e){
            return ['code'=>0,'msg'=>'操作异常:'.$e->getMessage()];
        }

    }

}
