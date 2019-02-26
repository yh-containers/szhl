<?php
namespace app\admin\controller;

class Product extends Common
{
    public function index()
    {
        $model = new \app\common\model\Product();
        $where=[];
        //绑定代理商用户
        if($this->proxy_id) {
            $list = $model->withJoin(['linkProxy'],'left')->where('linkProxy.proxy_id',$this->proxy_id)->paginate();
        }else{
            $list = $model->where($where)->paginate();
        }

//        $this->proxy_id && $where[] =['proxy_id','=',$this->proxy_id];
//        $list = $model->where($where)->paginate();
        return view('index',[
            'list'=>$list,
            'proxy_id'=>$this->proxy_id,
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
            $php_input['opt_uid'] = $this->user_id;
            $validate = new \app\common\validate\Product();
            return $model->actionAdd($php_input,$validate);
        }
        $where=[];
        //绑定代理商用户
        $this->proxy_id && $where[] =['proxy_id','=',$this->proxy_id];
        //项目详情
        $model = $model->where($where)->get($id);
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
        $proxy_id = $this->proxy_id;
        $where=[];
        //绑定代理商用户
        if($proxy_id){
            $proxy_id && $where[] =['linkProxy.proxy_id','=',$proxy_id];
            $model = $model->withJoin(['linkProxy'],'left')->where($where);
        }else{
            $model = $model->where($where);
        }

        $model = $model->with(['linkLogs'=>function($query)use($proxy_id){
            $where=[];
            $proxy_id && $where[] = ['proxy_id','=',$proxy_id];
            return $query->where($where);
        }])->get($id);
        //获取所有代理商
        $model_proxy = new \app\common\model\Manage();
        $proxy_users = $model_proxy->where([
            ['proxy_id','=',1],
            ['status','=',1]
        ])->select();
        return view('productDetail',[
            'model' => $model,
            'proxy_users' => $proxy_users,
            'proxy_id' => $proxy_id,
        ]);
    }

    //调整信息
    public function modifyInfo()
    {
        $php_input = $this->request->param();
        $id = $this->request->param('id',0,'intval');
        $status = $this->request->param('status',1,'intval');
        if($this->proxy_id){
            $model = new \app\common\model\ProxyProduct();
            //查看数据
            $model = $model->where([
                ['pid','=',$id],
                ['proxy_id','=',$this->proxy_id]
            ])->find();
            if(empty($model)){
                return ['code'=>0,'msg'=>'操作信息异常:'];
            }
            $state = $model->save(
                [
                    'status'=>$status,
                    'opt_uid'=>$this->user_id
                ],
                [
                    ['pid','=',$id],
                    ['proxy_id','=',$this->proxy_id]
                ]
            );
        }else{
            $model = new \app\common\model\Product();
            $php_input['opt_uid'] = $this->user_id;
            $state = $model->actionAdd($php_input);
        }

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

    /*
     * 项目委派
     * */
    public function delegate()
    {
        $id = $this->request->param('id',0,'intval');
        $proxy = $this->request->param('proxy',0,'intval');
        try{
            $model = new \app\common\model\Product();
            $model->delegate($id,$proxy,$this->user_id);
            return ['code'=>1,'msg'=>'操作成功'];
        }catch (\Exception $e){
            return ['code'=>0,'msg'=>'操作信息'.$e->getMessage()];
        }

    }
}
