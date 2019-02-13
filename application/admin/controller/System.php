<?php
namespace app\admin\controller;

class System extends Common
{
    public function index()
    {

        return view('index',[

        ]);
    }

    //管理人员
    public function manage()
    {


        $model = new \app\common\model\Manage();
        $where[] = ['proxy_id','=',$this->proxy_id];
        $list = $model->with(['linkRole'=>function($query){
            return $query->field('id,name');
        }])->where($where)->paginate();
        return view('manage',[
            'list'=>$list,
        ]);
    }

    //管理员添加
    public function manageAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Manage();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);
            //对应代理商/平台id
            $php_input['proxy_id'] = $this->proxy_id;

            $validate = new \app\common\validate\Manage();
            return $model->actionAdd($php_input,$validate);
        }
        $model = $model->get($id);
        //角色
        $model_role = new \app\common\model\Role();
        $model_role = $model_role->where('proxy_id',$this->proxy_id)->select();
        return view('manageAdd',[
            'model' => $model,
            'model_role' => $model_role,
        ]);
    }

    //管理员删除
    public function manageDel()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Manage();
        return $model->actionDel($id,$this->proxy_id);
    }

    //角色管理
    public function role()
    {
        $model = new \app\common\model\Role();
        $where[] = ['proxy_id','=',$this->proxy_id];

        $list = $model->where($where)->paginate();
        return view('role',[
            'list'=>$list,
        ]);

    }

    //管理员添加
    public function roleAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Role();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);
            //对应代理商/平台id
            $php_input['proxy_id'] = $this->proxy_id;

            $validate = new \app\common\validate\Role();
            return $model->actionAdd($php_input,$validate);
        }
        $model = $model->get($id);
        return view('roleAdd',[
            'model' => $model,
        ]);
    }

    //管理员删除
    public function roleDel()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Role();
        return $model->actionDel($id,$this->proxy_id);
    }
}
