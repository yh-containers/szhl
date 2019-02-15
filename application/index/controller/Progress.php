<?php
namespace app\index\controller;

class Progress extends Common
{
    //列表
    public function index()
    {


        return view('index',[

        ]);
    }

    /*
     * 详情
     * */
    public function detail()
    {

        return view('detail',[
        ]);
    }
}