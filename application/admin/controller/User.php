<?php
namespace app\admin\controller;

class User extends Common
{
    public function index()
    {
        $model = new \app\common\model\Users();
        $list = $model->paginate();
        return view('index',[
            'list'=>$list,
        ]);

    }

    public function welcome()
    {
        return view('welcome');
    }
}
