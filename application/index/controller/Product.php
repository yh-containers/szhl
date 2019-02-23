<?php
namespace app\index\controller;

class Product extends Common
{
    protected $need_login = true;

    protected $ignore_login_action ='index,showList,detail,search,flow';

    public function index()
    {

        $type = $this->request->param('type',1,'intval');
        //产品labels属性
        $lid = $this->request->param('lid');
        //产品labels
        $model_label = new \app\common\model\Label();
        $label_list = $model_label->select();
        return view('index',[
            'label_list' => $label_list,
            'type' => $type,
            'title_info' => \app\common\model\Product::getTypeInfo(),
            'lid' => $lid,
        ]);
    }

    /*
     * 产品列表
     * */
    public function showList()
    {

        $model = new \app\common\model\Product();

        $type = $this->request->param('type',1,'intval');
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
        $where[] = [
            ['type','=',$type],
        ];

        //关键字
        $keyword = $this->request->param('keyword','','trim');
        $keyword && $where[] = ['name','like','%'.$keyword.'%'];

        if($lid){
            foreach($lid as $vo){
                is_numeric($vo) && $vo>0 && $where[] =['','EXP',\think\Db::raw("FIND_IN_SET($vo,labels)")];
            }
        }
        $list = $model->where($where)->paginate()->each(function(&$item, $key){
            $item= [
                'id' => $item['id'],
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
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Product();
        $model = $model->get($id);
        $model && $model->setInc('view');
        return view('detail',[
            'model' => $model,
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


        list($data,$tip) = [[],''];
        $model && list($data,$tip) = $model->reqFlow($step);
        return view($step_page[$step],[
            'model'=>$model,
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
        $step = $this->request->param('step',0,'intval');
        $php_input = $this->request->param();
        $validate = new \app\common\validate\ProductReq();
        $validate->scene(self::SCENE.'_req'.$step);
        $model = new \app\common\model\ProductReq();
        //绑定用户id
        $php_input['uid']= $this->user_id;
        $result = $model->actionAdd($php_input,$validate);

        if($result['code']){
            $result['url'] = url('flow',['pid'=>$php_input['pid'],'step'=>++$step,'id'=>empty($php_input['id'])?$model->getKey():$php_input['id']]);
        }
        return $result;
    }
}