<?php
namespace app\admin\controller;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

class User extends Common
{
    public function index()
    {
        $model = new \app\common\model\Users();
        $where=[];
        //绑定代理商用户
        $this->proxy_id && $where[] =['proxy_id','=',$this->proxy_id];
        $list = $model->where($where)->paginate();
        return view('index',[
            'list' => $list,
            'proxy_id' => $this->proxy_id
        ]);

    }

    public function userDetail()
    {
        $id = $this->request->param('id',0,'intval');
        $where[]=['id','=',$id];
        //绑定代理商用户
        $this->proxy_id && $where[] =['proxy_id','=',$this->proxy_id];
        $model = new \app\common\model\Users();
        $model = $model->with(['linkDirectFuid','linkMineReq'])->where($where)->find();
        return view('userDetail',[
            'model' => $model
        ]);
    }

    //调整用户信息
    public function modifyInfo()
    {
        $php_input = $this->request->param();
        $model = new \app\common\model\Users();
        $state = $model->actionAdd($php_input);
        return ['code'=>(int)$state,'msg'=>$state?'修改成功':'修改异常'];
    }
    //二维码
    public function qrcode($path='',$content='')
    {
        //资源路径
        $resource_path = str_replace('\\','/',\Env::get('vendor_path').'\\endroid\\qr-code');
        // Create a basic QR code
        $qrCode = new QrCode($content);
        $qrCode->setSize(300);

// Set advanced options
        $qrCode
            ->setWriterByName('png')
            ->setMargin(10)
            ->setEncoding('UTF-8')
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH)
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255])
//            ->setLabel('Scan the code', 16, $resource_path.'/assets/noto_sans.otf', LabelAlignment::CENTER)
//            ->setLogoPath($resource_path.'/assets/symfony.png')
            ->setLogoWidth(100)
            ->setValidateResult(false)
        ;
        $root_path = str_replace('\\','/',\Env::get('root_path'));
        //logo
        if($path){
            $qrCode->setLogoPath($root_path.$path);
        }
        // Directly output the QR code
        return response($qrCode->writeString())->header('Content-Type',$qrCode->getContentType());
//        echo $qrCode->writeString();
        // Save it to a file
//        $qrCode->writeFile(__DIR__.'/qrcode.png');
//
//        // Create a response object
//        $response = new QrCodeResponse($qrCode);
    }


    //代理用户
    public function proxy()
    {
        $model = new \app\common\model\Manage();
        $list = $model->where('proxy_id',1)->paginate();
        return view('proxy',[
            'list'=>$list,
        ]);
    }

    //用户添加
    public function add()
    {
        $type = $this->request->param('type',2,'intval');
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Users();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);
            //对应代理商/平台id--编辑无法修改用户原有属性
            if(empty($php_input['id'])){
                $php_input['proxy_id'] = $this->proxy_id;
                $php_input['type'] = $type;
            }
            $validate = new \app\common\validate\Users();
            $validate->scene(self::SCENE.'_add');
            return $model->actionAdd($php_input,$validate);
        }

        $model = $model->where('id',$id)->find();
        return view('add',[
            'model'=>$model,
            'type'=>$type,
        ]);
    }

    //代理用户添加
    public function proxyAdd()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Manage();

        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            if(empty($php_input['password']) && isset($php_input['password'])) unset($php_input['password']);
            //对应代理商
            $php_input['proxy_id'] = 1;

            $validate = new \app\common\validate\Manage();
            $validate->scene(self::SCENE.'_add_proxy');
            return $model->actionAdd($php_input,$validate);
        }

        $model = $model->where('id',$id)->find();
        return view('proxyAdd',[
            'model'=>$model,
        ]);
    }


    //代理商申请
    public function proxyReq()
    {
        $model = new \app\common\model\ProxyReq();
        $list = $model->paginate();
        return view('proxyReq',[
            'list'=>$list,
        ]);
    }

    //代理审核处理
    public function handleProxyReq()
    {
        $id = $this->request->param('id',0,'intval');
        $php_input = $this->request->param();
        try{
            $php_input['opt_uid'] = $this->user_id;
            $model = new \app\common\model\ProxyReq();
            $model->handleReq($id,$php_input);
            return ['code'=>1,'msg'=>'操作成功'];
        }catch (\Exception $e){
            return ['code'=>0,'msg'=>'操作异常:'.$e->getMessage()];
        }
    }

}
