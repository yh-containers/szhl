<?php
namespace app\admin\controller;

class Product extends Common
{
    public function index()
    {
        $keyword = $this->request->param('keyword','','trim');
        $model = new \app\common\model\Product();
        $where=[];

        if($keyword){
            $where[] = ['name','like','%'.$keyword.'%'];
        }

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
            'keyword'=>$keyword,
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
            //对应代理商/平台id
            $php_input['proxy_id'] = $this->proxy_id;
            $php_input['opt_uid'] = $this->user_id;
            //重组labels标签
            $php_input['labels'] = empty($php_input['labels'])?[]:(isset($php_input['type'])?isset($php_input['labels'])?$php_input['labels'][$php_input['type']]:[]:[]);
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

        $type_label = \app\common\model\Product::$type_label;
        $type_label_data = [];
        foreach ($type_label as $vo) {
            $type_label_data[$vo['type']]=array_merge($vo,['data'=>[]]);
        }

        $model_label->where('status',1)->select()->each(function($item,$index)use(&$type_label_data){
            isset($type_label_data[$item['type']]) && $type_label_data[$item['type']]['data'][] = $item->toArray();
        });;

        return view('productAdd',[
            'model' => $model,
            'money_unit' => \app\common\model\Product::moneyUnit(),
            'auth_unit' => \app\common\model\Product::authUnit(),
            'per_unit' => \app\common\model\Product::perUnit(),
            'type_label_data' => $type_label_data,
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
        $type_label = \app\common\model\Product::$type_label;
        $type_label_data = [];
        foreach ($type_label as $vo) {
            $type_label_data[$vo['type']]=array_merge($vo,['data'=>[]]);
        }
        $model->order('sort','asc')->select()->each(function($item,$index)use(&$type_label_data){
            isset($type_label_data[$item['type']]) && $type_label_data[$item['type']]['data'][] = $item->toArray();
        });

        return view('lables',[
            'type_label_data'=>$type_label_data,
            'label_fields_status'=>\app\common\model\Label::$fields_status,
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
            'type_label' => \app\common\model\Product::$type_label,
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
