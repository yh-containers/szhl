<?php
namespace app\admin\controller;

class Product extends Common
{
    public function index()
    {
        $model = new \app\common\model\Product();
        $list = $model->paginate();
        return view('index',[
            'list'=>$list,
        ]);

    }


    //添加
    public function productAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Product();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);
            //对应代理商/平台id
            $php_input['proxy_id'] = $this->proxy_id;

            $validate = new \app\common\validate\Product();
            return $model->actionAdd($php_input,$validate);
        }
        $model = $model->get($id);
        return view('productAdd',[
            'model' => $model,
            'money_unit' => \app\common\model\Product::moneyUnit(),
            'auth_unit' => \app\common\model\Product::authUnit(),
            'per_unit' => \app\common\model\Product::perUnit(),
        ]);
    }


    //产品详情
    public function productDetail()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Product();
        $model = $model->with(['linkLogs'])->get($id);
        return view('productDetail',[
            'model' => $model,
        ]);
    }

    //调整用户信息
    public function modifyInfo()
    {
        $php_input = $this->request->param();
        $model = new \app\common\model\Product();
        $state = $model->actionAdd($php_input);
        return ['code'=>(int)$state,'msg'=>$state?'修改成功':'修改异常'];
    }
}
