<?php
/**
 * Created by PhpStorm.
 * User: Ru
 * Date: 2019/2/15
 * Time: 12:02
 */

namespace app\admin\controller;


class Message extends Common
{
    public function index()
    {

    }
    public function article()
    {
        $model = new \app\common\model\Article();
        $list = $model->paginate();
        return view('index',[
            'list'=>$list,
        ]);
    }
    public function articleAdd()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Article();
        //表单提交
        if($this->request->isAjax()){
            $php_input = $this->request->param();
            $php_input['send_time'] = time();
            $validate = new \app\common\validate\Article();
            return $model->actionAdd($php_input,$validate);
        }
        $model = new \app\common\model\Article();
        $model = $model->get($id);
        return view('articleAdd',[
            'model' => $model,
        ]);
    }
    public function articleDel()
    {
        $id = $this->request->param('id');
        $model = new \app\common\model\Article();
        return $model->articleDelete($id);
    }
}