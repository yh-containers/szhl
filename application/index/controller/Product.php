<?php
namespace app\index\controller;

class Product extends Common
{
    protected $need_login = true;

    protected $ignore_login_action ='index,showList,detail,search,flow';

    public function index()
    {

        $type = $this->request->param('type',0,'intval');
        //产品labels属性
        $lid = $this->request->param('lid');
        //产品labels
        $model_label = new \app\common\model\Label();
        $label_list = $model_label->where('status',1)->where('type',$type)->select();
        return view('index',[
            'label_list' => $label_list,
            'type' => $type,
            'title_info' => \app\common\model\Product::getTypeInfo($type),
            'lid' => $lid,
            'proxy_id' => $this->proxy_id,
        ]);
    }

    /*
     * 产品列表
     * */
    public function showList()
    {
        $where =[];
        $model = new \app\common\model\Product();

        $type = $this->request->param('type',0,'intval');
        //产品labels属性
        $lid = $this->request->param('lid');
        $lid = explode(',',$lid);
        //利率从低到高
        $order_per = $this->request->param('per','','trim');
        if($order_per){
            $order_per = $order_per=='asc'?'asc':'desc';
            $model = $model->order('per',$order_per);
        }
        //奖金从高到低
        $order_commission = $this->request->param('commission','','trim');
        if($order_commission){
            $order_commission = $order_commission=='asc'?'asc':'desc';
            $model = $model->order('commission',$order_commission);
        }

        //指定奖金金额
        $money_start = $this->request->param('money_start',0);
        if($money_start){
            $model = $model
                ->where(function ($query) use($money_start) {
                    $query->whereOr([
                        [
                            ['money_start','elt',$money_start],
                            ['money_end','egt',$money_start],
                        ],
                        [
                            ['money_start','=',$money_start]
                        ]
                    ]);
                });

        }

        //期限查询
        $auth_time_start = $this->request->param('auth_time_start');
        if($auth_time_start) {
            $prefix_auth_time = substr($auth_time_start,0,1);
            if($prefix_auth_time=='+'){
                //多少月以上
                $model = $model->where('auth_time_start','elt',intval($auth_time_start));
            }else{
                $model = $model
                    ->where(function ($query) use($auth_time_start) {
                        $query->whereOr([
                            [
                                ['auth_time_start','elt',$auth_time_start],
                                ['auth_time_end','egt',$auth_time_start],
                            ],
                            [
                                ['auth_time_start','=',$auth_time_start]
                            ]
                        ]);
                    });
            }
        }
        //按类型查询
        $type && $where[] = ['type','=',$type];


        //关键字
        $keyword = $this->request->param('keyword','','trim');
        if($keyword){
            $keyword = iconv('gbk','utf-8',$keyword);
            $keyword && $where[] = ['name','like','%'.$keyword.'%'];
        }

        if($lid){
            foreach($lid as $vo){
                is_numeric($vo) && $vo>0 && $where[] =['','EXP',\think\Db::raw("FIND_IN_SET($vo,labels)")];
            }
        }
        //代理/指定代理商邀请的用户
        if($this->user_type==2 || $this->proxy_id){
            $model = $model->withJoin(['linkProxy'],'left')->where('Product.status',1)->where('linkProxy.status',1)->where('linkProxy.proxy_id',$this->proxy_id);
        }else{
            $model = $model->where('status',1);
        }
        //按检索条件查询
        if($this->request->has('match_id')){
            $match_id = $this->request->param('match_id',0,'intval');
            if($match_id){
                $model_match = new \app\common\model\ProductSearch();
                $match_info = $model_match->where('id',$match_id)->find();
            }
            if(!empty($match_info)){
                $match_where = [];
                if($match_info['content']){
                    foreach ($match_info['content'] as $vo){
                        $match_where[] = $vo['col_id'].'-'.$vo['parent_id'];
                    }
                }
                //检索匹配的产品
                $product_ids = (new \app\common\model\ProductChoose())->whereIn('choose_data',$match_where)->column('pid');
                $product_ids = array_unique($product_ids); //唯一
                $where[] = ['id','in',$product_ids];
                //检索金额
                $where[] = ['money_start','egt',$match_info['money']];
            } else {
                $where[] = ['id','=',0]; //强行无结果
            }

        }




        $list = $model->where($where)->paginate()->each(function(&$item, $key){
            $item= [
                'id' => $item['id'],
                'proxy_pro_id' => isset($item['link_proxy'])?$item['link_proxy']['id']:0,//代理产品id
                'name' => $item['name'],
                'per' => $item['per'],
                'per_unit' => \app\common\model\Product::$per_unit[$item['per_unit']],
                'auth_time_start'=> $item['auth_time_start'],
                'auth_time_end'=> $item['auth_time_end'],
                'auth_unit'=> \app\common\model\Product::$auth_unit[$item['auth_unit']],
                'money_start'=> $item['money_start'],
                'money_end'=> $item['money_end'],
                'money_unit'=> \app\common\model\Product::$money_unit[$item['money_unit']],
                'intro'=> $item['intro'],
                'is_hot'=> $item['is_hot'],
            ];
            return $item;
        });

        return ['code'=>1,'msg'=>'获取成功','data'=>$list];
    }

    /*
     * 详情
     * */
    public function detail()
    {
        $search_type = $this->request->param('search_type');
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Product();
        //代理
        if($this->user_type==2){
            $model = $model->withJoin(['linkProxy'],'left')->where('linkProxy.proxy_id',$this->proxy_id);
        }
        $model = $model->get($id);
        $model && $model->setInc('view');//新增浏览次数
        return view('detail',[
            'model' => $model,
            'search_type' => $search_type,
        ]);
    }

    /*
     * 产品搜索
     * */
    public function search()
    {
        $keyword = $this->request->param('keyword','','trim');
        return view('search',[
            'keyword'=>$keyword,
        ]);
    }

    /*
     * 申请流程
     * */
    public function flow()
    {
        //流程页面
        $step_page = ['flowStepOne','flowStepTwo','flowStepThree'];

        //流程id
        $id = $this->request->param('id',0,'intval');
        //项目id
        $pid = $this->request->param('pid',0,'intval');
        //流程步骤
        $step = $this->request->param('step',0,'intval');

        //项目信息
        $model = new \app\common\model\Product();
        $model = $model->get($pid);
        //项目条件
        $pro_cond= [];
        !empty($model) && $pro_cond=(new \app\common\model\ProductTypeSpu())
            ->with(['linkSpu'])->where(['type'=>$model['type']])->order('sort','asc')->select()->toArray();
        foreach ($pro_cond as &$vo) {
            if(isset($vo['link_spu']['content'])){
                foreach ($vo['link_spu']['content'] as &$item){
                    $item['spu_type'] = $vo['type'];
                    $item['spu_sc_id'] = $vo['sc_id'];      //spu_coll的id
                }
                $vo['link_spu']['content'] = arrayToTree2($vo['link_spu']['content']);
            }

        }
//        dump($pro_cond);exit;
        list($data,$tip) = [[],''];
        $model && list($data,$tip) = $model->reqFlow($step);
        return view($step_page[$step],[
            'model'=>$model,
            'pro_cond'=>$pro_cond,
            'data'=>$data,
            'tip'=>$tip,
            'step'=>$step,
            'id'=>$id,
        ]);
    }

    /*
     * 流程提交
     * */
    public function flowAction()
    {
        //流程步骤
        $submit = $this->request->param('submit');
        $match_id = $this->request->param('match_id',0,'intval');
        $step = $this->request->param('step',0,'intval');
        $php_input = $this->request->param();
        $validate = new \app\common\validate\ProductReq();
        if($submit == 'match'){
            //产品匹配提交
            $validate->scene(self::SCENE.'_match');
            $model_match = new \app\common\model\ProductSearch();
            $match_info = $model_match->get($match_id);
            $php_input['content'] = $match_info['content'];
            $step=1; //跳过第一步
        }else{
            //正常流程
            $validate->scene(self::SCENE.'_req'.$step);
        }
        $model = new \app\common\model\ProductReq();
        //绑定用户id
        $php_input['uid']= $this->user_id;
        $result = $model->actionAdd($php_input,$validate);

        if($result['code']){
            $result['url'] = url('flow',['pid'=>$php_input['pid'],'step'=>++$step,'id'=>empty($php_input['id'])?$model->getKey():$php_input['id']]);
        }
        return $result;
    }


    /*
     * 产品快速匹配
     * */
    public function match()
    {
        $match_id = $this->request->param('match_id',0,'intval');
        if(!$match_id && $this->request->isAjax()){
            $model = new \app\common\model\ProductSearch();
            $php_input = $this->request->param();
            $php_input['uid'] = $this->user_id;
            $model->save($php_input);
            return ['code'=>1,'msg'=>'前往匹配界面','url'=>url('',['match_id'=>$model->id])];
        }else{

            return view('matchResult',[
                'match_id' => $match_id
            ]);
        }


    }

    /*
     * 匹配购买
     * */
    public function matchFlow()
    {
        //项目id
        $pid = $this->request->param('pid',0,'intval');
        //检索id
        $match_id = $this->request->param('match_id',0,'intval');
        //项目信息
        $model = new \app\common\model\Product();
        $model = $model->get($pid);
        $model_match = new \app\common\model\ProductSearch();
        $match_info = $model_match->field('id,money')->where('id',$match_id)->find();

        return view('matchFlow',[
            'model'=>$model,
            'match_info'=>$match_info,
        ]);
    }
}