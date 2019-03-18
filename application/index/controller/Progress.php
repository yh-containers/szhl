<?php
namespace app\index\controller;

class Progress extends Common
{
    protected $need_login = true;
    //列表
    public function index()
    {
        $status = $this->request->param('status',null,'intval');

        return view('index',[
            'status' => $status,
        ]);
    }

    public function showList()
    {
        $status = $this->request->param('status',null,'intval');
        $model = new \app\common\model\ProductReq();
        $where = [];
        if($status==1){
            //审批中
            $where[] = ['status','=',1];
            $where[] = ['auth_status','=',0];
        }elseif($status==2){
            //面谈中
            $where[] =  ['status','=',1];
            $where[] =  ['face_status','=',0];
            $where[] =  ['auth_status','=',1];
        }elseif($status==3){
            //已放款
            $where[] =  ['send_award_status','=',1];
        }elseif($status==4){
            //已被拒
            $where[] =  ['auth_status','=',2];
        }elseif($status==5){
            //未放款
            $where[] =  ['auth_status','=',1];
            $where[] =  ['face_status','=',1];
            $where[] =  ['send_award_status','=',0];
        }
        $where[] =['uid','=',$this->user_id];
        $list = $model->where($where)->where('status','gt',0)->order('id','desc')->paginate()->each(function(&$item, $key)use(&$arr){

            list($status_color,$status_name,$status_intro) = $item->getStatusInfo();
            $item= [
                'id'        => $item['id'],
                'no'        => $item['no'],
                'name'      => $item['product_info']['name'],
                'auth_time' => $item['auth_time'],
                'auth_unit' => $item['auth_unit'],
                'auth_unit_name' => \app\common\model\Product::$auth_unit[$item['auth_unit']],
                'money'     => $item['money'],
                'money_unit'=>  $item['money_unit'],
                'money_unit_name'=>  \app\common\model\Product::$money_unit[$item['money_unit']],
                'status'    => $item['status'],
                'face_status'    => $item['face_status'],
                'auth_status'    => $item['auth_status'],
                'status_name'=> $status_name,
                'status_color'=> $status_color,
                'status_intro'=> $status_intro,

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
        $model = new \app\common\model\ProductReq();
        $model = $model->with(['linkLogs'])->get($id);

        return view('detail',[
            'model' => $model,
            'money_unit' => \app\common\model\Product::$money_unit,
            'auth_unit'  => \app\common\model\Product::$auth_unit,
        ]);
    }

    public function applyData()
    {
        $req_id = $this->request->param('req_id',0,'intval');
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $model_req = new \app\common\model\ProductReq();
            $result = $model_req->actionAdd($php_input);
            return $result;
        }

        //查询已选的属性
        $model_setting = new \app\common\model\Setting();
        $content = $model_setting->getContent('perfect_data');
        $data = $content?explode(',',$content):[];
        $one_data=(new \app\common\model\ProductSpuCol())->whereIn('id',$data)->order('sort','asc')->select()->toArray();
        $one_data = array_column($one_data,null,'id');
        $result_data = [];
        foreach ($data as $vo) {
            $info = isset($one_data[$vo])?$one_data[$vo]:[];
            if($info){
                foreach ($info['content'] as &$item){
                    $item['spu_type'] = 0;      //spu_coll的id
                    $item['spu_sc_id'] = $info['id'];      //spu_coll的id
                }
                $info['content'] = arrayToTree2($info['content']);
                $result_data[]= $info;
            }


        }
        $model = new \app\common\model\ProductReq();
        $model = $model->get($req_id);

        return view('applyData',[
            'one_data' => $result_data,
            'req_id' => $req_id,
            'model' => $model,
        ]);
    }


//    public function applyData()
//    {
//        $req_id = $this->request->param('req_id',0,'intval');
//        $model = new \app\common\model\ProductReq();
//        $model = $model->where(['id'=>$req_id,'uid'=>$this->user_id])->find();
//        if($this->request->isAjax()){
//            $model_content = $model['content']?$model['content']:[];
//            $content = $this->request->param('content');
//            foreach ($content as $key=>$vo) {
//                if(!empty($model_content)){
//                    if(isset($model_content[$key])){
//                        $model_content[$key]['info'] = $vo;
//                    }
//                }
//            }
//            $model->content = $model_content;
//            $model->save();
//            return ['code'=>1,'msg'=>'修改成功'];
//        }
//
//
//        return view('applyData',[
//            'model' => $model,
//        ]);
//    }
    /*
     * 合同信息
     * */
    public function contract()
    {
//        dump(\Env::get('root_path'));exit;
        $req_id = $this->request->param('req_id',0,'intval');
        $model = new \app\common\model\ProductReq();
        $model = $model->where(['id'=>$req_id,'uid'=>$this->user_id])->find();

        if(empty($model)){
            //不是自己的申请
            return $this->request->isAjax()?['code'=>0,'msg'=>'申请流程异常']:view('contract',['content'=>'申请流程异常','req_id'=>$req_id]);
        }

        if($this->request->isAjax()){ //同意合同
            try{
                $model->contract($req_id,0,$this->user_id);
                return ['code'=>1,'msg'=>'操作成功'];
            }catch (\Exception $e){
                return ['code'=>0,'msg'=>'操作异常'.$e->getMessage()];
            }
        }
        return view('contract',[
            'req_id' => $req_id
        ]);
    }

    /*
     * 生成合同
     * */
    public function generateContract()
    {
        $model_user = (new \app\common\model\Users())->where('id',$this->user_id)->find();
        $content = \app\common\service\temp\Contract::changeContent($model_user);
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>中翰哲</title>
    <meta name="keywords" content="中瀚哲">
    <meta name="description" content="中瀚哲">
    <style>


body {
font-family:simsun;
}</style>
</head>
<body>
'.$content.'
</body>
</html>';
//        return $html;
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setBasePath(\Env::get('root_path'));
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');
        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }

    /*
     * 查看
     * */
    public function lookContract()
    {
        $req_id = $this->request->param('req_id',0,'intval');
        $model = new \app\common\model\ProductReq();
        $content = $model->where(['id'=>$req_id,'uid'=>$this->user_id])->value('contract_content');
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>中翰哲</title>
    <meta name="keywords" content="中瀚哲">
    <meta name="description" content="中瀚哲">
    <style>


body {
font-family:simsun;
}</style>
</head>
<body>
'.$content.'
</body>
</html>';
//        return $html;
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setBasePath(\Env::get('root_path'));
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');
        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream();
    }
}