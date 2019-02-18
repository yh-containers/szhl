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
        //项目详情
        $model = $model->get($id);
        //项目标签
        $model_label = new \app\common\model\Label();
        $label_list = $model_label->where('status',1)->select();

        return view('productAdd',[
            'model' => $model,
            'label_list' => $label_list,
            'money_unit' => \app\common\model\Product::moneyUnit(),
            'auth_unit' => \app\common\model\Product::authUnit(),
            'per_unit' => \app\common\model\Product::perUnit(),
            'type_label' => \app\common\model\Product::$type_label,
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
    public function labels()
    {
        $model = new \app\common\model\Label();
        $list = $model->order('sort','asc')->select();
        return view('lables',[
            'list'=>$list,
        ]);

    }
    public function labelsAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Label();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            return $model->actionAdd($php_input);
        }
        $model = new \app\common\model\Label();
        $model = $model->get($id);
        return view('labelsAdd',[
            'model' => $model,
        ]);
    }
    public function labelsDel()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Label();
        return $model->actionDel($id);
    }
    public function labelsSort()
    {
        $id = $this->request->param('id');
        $sort = $this->request->param('sort');
        $model = new \app\common\model\Label();
        return $model->setSort($id,$sort);
    }
}
