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
            //严重筛选项
            $choose_item_data = [];
            $choose_item = $this->request->param('choose_data');
            foreach ($choose_item as $vo){
                $choose_item_data[]=['choose_data'=>$vo];
            }
            $validate = new \app\common\validate\Product();

            if ($validate && !$validate->check($php_input)) {
                return ['code'=>0,'msg'=>$validate->getError()];
            }

            try{
                $model->startTrans();
                if(!empty($id)){  //编辑状态
                    $model_info = $model->find($id);
                    if(!empty($model_info)){
                        //删除入库数据
//                        $model_info->together('linkChoose')->delete();
                        (new \app\common\model\ProductChoose())->where(['pid'=>$id])->delete();
                        $model_info->save($php_input,['id'=>$id]);
                        $model_info->linkChoose()->saveAll($choose_item_data);
                    }
                }else{
                    //清除主键影响
                    unset($php_input['id']);
                    $model->save($php_input);
                    $model->linkChoose()->saveAll($choose_item_data);
                }
                $model->commit();
            }catch (\Exception $e) {
                $model->rollback();
                return ['code'=>0,'msg'=>'操作异常'.$e->getMessage()];
            }
            return ['code'=>1,'msg'=>'操作成功'];

        }
        $where=[];
        //绑定代理商用户
        $this->proxy_id && $where[] =['proxy_id','=',$this->proxy_id];
        //项目详情
        $model = $model->where($where)->get($id);
        //项目标签
        $model_label = new \app\common\model\Label();

        //项目类型
        $type_label = \app\common\model\Product::$type_label;
        $type_label_data = $labels_type = [];
        foreach ($type_label as $vo) {
            $labels_type[] = $vo['type'];
            $type_label_data[$vo['type']]=array_merge($vo,['data'=>[]]);
        }
        //项目贷款信息
        $type_spu_data = [];
        $type_spu = (new \app\common\model\ProductTypeSpu())
            ->with(['linkSpu'])
            ->whereIn('type',$labels_type)->select()->toArray();
        foreach ($type_spu as $vo) {
            //强制写入id sc_id
            if($vo['link_spu']){
                foreach($vo['link_spu']['content'] as &$item){
                    $item['type_id'] = $vo['link_spu']['id'];
                    $item['type_sid'] = $vo['link_spu']['sid'];
                }

            }
            if(array_key_exists($vo['type'],$type_spu_data)){
                $type_spu_data[$vo['type']]['data'][] = $vo;
            }else{
                $type_spu_data[$vo['type']]=[
                    'type' => $vo['type'],
                    'data' => [$vo],
                ];
            }
        }
        $type_spu_data = array_values($type_spu_data);
//        dump($type_spu_data);exit;
        //项目类型对应的数据

        $model_label->where('status',1)->select()->each(function($item,$index)use(&$type_label_data){
            isset($type_label_data[$item['type']]) && $type_label_data[$item['type']]['data'][] = $item->toArray();
        });
        //所选项
        $choose_data = [];
        !empty($model) && $choose_data = array_column($model->linkChoose->toArray(),'choose_data');

        return view('productAdd',[
            'model' => $model,
            'type_spu_data' => $type_spu_data,
            'choose_data' => $choose_data,
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

    /*
     * 产品spu
     * */
    public function spu()
    {
        $keyword = $this->request->param('keyword','','trim');
        $model = new \app\common\model\ProductSpu();
        $where=[];

        if($keyword){
            $where[] = ['name','like','%'.$keyword.'%'];
        }

        $list = $model->with(['linkCol'=>function($query){
            return $query->order('sort','asc');
        }])->where($where)->paginate();

        return view('spu',[
            'list'=>$list,
            'proxy_id'=>$this->proxy_id,
            'keyword'=>$keyword,
        ]);
    }

    public function spuAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\ProductSpu();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            return $model->actionAdd($php_input);
        }
        $model = $model->get($id);
        return view('spuAdd',[
            'model' => $model,
            'type_label' => \app\common\model\Product::$type_label,
        ]);
    }


    public function spuDel()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\ProductSpu();
        return $model->actionDel($id);
    }

    /*
     * 产品spuCol
     * */
    public function spuCol()
    {
        $spu_id = $this->request->param('spu_id',0,'intval');
        //spu信息
        $model_spu = new \app\common\model\ProductSpu();
        $spu_info = $model_spu->get($spu_id);

        $model = new \app\common\model\ProductSpuCol();
        $where=[];
        $where[] =['sid','=',$spu_id];

        $list = $model->where($where)->select();

        return view('spuCol',[
            'list'=>$list,
            'proxy_id'=>$this->proxy_id,
            'spu_info'=>$spu_info,
        ]);
    }

    /*
     * 产品spuCol--新增或编辑
     * */
    public function spuColAdd()
    {
        $spu_id = $this->request->param('spu_id');
        $id = $this->request->param('id');
        $model = new \app\common\model\ProductSpuCol();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $php_input['content'] = $this->request->param('content',[]);
            return $model->actionAdd($php_input);
        }

        //产品spu信息
        $model_spu = new \app\common\model\ProductSpu();
        $spu_info = $model_spu->get($spu_id);

        $model = $model->get($id);
        return view('spuColAdd',[
            'spu_info' => $spu_info,
            'model' => $model,
            'type_label' => \app\common\model\Product::$type_label,
        ]);
    }


    public function spuColDel()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\ProductSpuCol();
        return $model->actionDel($id);
    }


    /*
     * 产品条件
     * */
    public function productCond()
    {
        $type = $this->request->param('type',0,'intval');
        $model_type_spu = new \app\common\model\ProductTypeSpu();

        if($this->request->isAjax()){
            $spu_col = $this->request->param('spu_col');
            if(empty($spu_col)) return ['code'=>0,'msg'=>'请选择对应属性'];
            $save_data = [];
            foreach($spu_col as $key=>$vo){
                $save_data[] = [
                    'type'      => $type,
                    'sc_id'     => $vo,
                    'sort'      => $key,
                ];
            }

            try{
                $model_type_spu->startTrans();
                //移除属性
                $model_type_spu->where(['type'=>$type])->delete();

                //增加属性
                $model_type_spu->saveAll($save_data);
                $model_type_spu->commit();
                return ['code'=>1,'msg'=>'操作成功'];
            }catch (\Exception $e){
                $model_type_spu->rollback();
                return ['code'=>0,'msg'=>'操作异常:'.$e->getMessage()];
            }
        }

        //查询已选的属性
        $exist_spu_data = $model_type_spu->withJoin(['linkSpu'],'right')->where(['Product_type_spu.type'=>$type])->order('sort','asc')->select()->toArray();
        $model = new \app\common\model\ProductSpu();
        $list = $model->with(['linkCol'=>function($query){
            return $query->order('sort','asc');
        }])->where(['status'=>1])->order('sort','asc')->select()->toArray();
        return view('productCond',[
            'type' => $type,
            'list' => $list,
            'exist_spu' => array_column($exist_spu_data,'sc_id'),
            'exist_spu_data' => $exist_spu_data,
        ]);
    }

    /*
     * 用户搜索条件
     * */
    public function productSearchCond()
    {
        $model_setting = new \app\common\model\Setting();

        if($this->request->isAjax()){
            $ids = $this->request->param('content.ids');
            $data = [];
            $index=0;
            foreach ($ids as $key=>$vo) {
                if($vo=='index' && $key!=0){
                    $index++;
                }elseif($vo!='index'){
                    $data[$index][]=$vo;
                }
            }
            if(empty($data)){
                return ['code'=>0,'msg'=>'请选择需要操作的选项'];
            }
            (new \app\common\model\Setting())->setContent('user_search',json_encode($data));
            return ['code'=>1,'msg'=>'保存成功'];
        }

        //查询已选的属性
        $choose_list = $exist_spu = [];
        $content = $model_setting->getContent('user_search');
        $choose_item = $content?json_decode($content,true):[];
        foreach ($choose_item as $vo){
            $exist_spu = array_merge($exist_spu,$vo);
        }
        $model = new \app\common\model\ProductSpu();
        $list = $model->with(['linkCol'=>function($query){
            return $query->order('sort','asc');
        }])->where(['status'=>1])->order('sort','asc')->select()->toArray();
        //已选中的几项
        $exist_spu && $choose_list = (new \app\common\model\ProductSpuCol())->field('id,name')->whereIn('id',$exist_spu)->order('sort','asc')->select()->toArray();
        $exist_spu && $choose_list && $choose_list = array_column($choose_list,null,'id');
        return view('productSearchCond',[
            'type' => 0,
            'list' => $list,
            'exist_spu' => $exist_spu,
            'choose_item' => $choose_item,
            'choose_list' => $choose_list,
        ]);
    }


}
