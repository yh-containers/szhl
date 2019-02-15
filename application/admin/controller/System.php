<?php
namespace app\admin\controller;

use app\common\model\Setting;

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

    //服务协议
    public function protocol()
    {

        $model = new \app\common\model\Setting();
        $content = $model->getContent('protocol');
        return view('protocol',[
            'content'=>$content,
        ]);
    }

    //常用缓存保存动作
    public function settingAction()
    {

        $type = $this->request->param('type');
        $content = $this->request->param('content');
        $content = is_array($content)?json_encode($content):$content;
        $model = new \app\common\model\Setting();
        $status = $model->setContent($type,$content);
        return ['code'=>1,'msg'=>'操作成功'];



    }

    //地域信息
    public function location()
    {

        $model = new \app\common\model\Location();
        $locations = $model
            ->field('*,area_id as id')
            ->with(['linkLocation'=>function($qeury){
                return $qeury->field('*,area_id as id');
            }])
            ->where('area_type',1)->select();

        return view('location',[
            'data'=>$locations,
        ]);
    }
    public function locationChoose(){
        $is_check = $this->request->param('is_check');
        $area_id = $this->request->param('area_id');

        $model = new \app\common\model\Location();
        $status = $model->setContent($is_check,$area_id);
        return ['code'=>1,'msg'=>'操作成功'];
    }


    //系统设置
    public function setting()
    {
        $model = new \app\common\model\Setting();
        $company_info = $model->getContent('company_info');
        $company_intro = $model->getContent('company_intro');
        return view('setting',[
            'company_info' => json_decode($company_info,true),
            'company_intro' => $company_intro,
        ]);
    }


    //轮播图管理
    public function flowImage()
    {
        $model = new \app\common\model\FlowImage();

        $list = $model->order('sort','asc')->paginate();
        return view('flowImage',[
            'list'=>$list,
        ]);
    }

    //轮播图管理--添加
    public function flowImageAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\FlowImage();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();

            $validate = new \app\common\validate\FlowImage();
            return $model->actionAdd($php_input,$validate);
        }
        $model = $model->get($id);
        return view('flowImageAdd',[
            'model' => $model,
        ]);
    }

    //轮播图管理--删除
    public function flowImageDel()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\FlowImage();
        return $model->actionDel($id);
    }
    public function suggestion()
    {
        $model = new \app\common\model\Suggest();

        $list = $model
            ->with(['linkUser'=>function($qeury){

                    return $qeury->field('*');
            }])
            ->order('create_time','desc')->paginate();

        return view('suggestion',[
            'list'=>$list,
        ]);
    }


}
