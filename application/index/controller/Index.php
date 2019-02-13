<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return view('index',[

        ]);
    }

    //用户登录
    public function login()
    {

        return view('login',[

        ]);
    }

    //用户登录
    public function reg()
    {

        return view('reg',[

        ]);
    }
}
