<?php
namespace app\admin\middleware;


class CheckAuth
{
    const IGNORE_ACTION = 'index/index,login/login,login/proxylogin,login/logout,index/welcome';

    private $user_id = 0;
    private $proxy_id = 0;
    private $role_id = 0;
    //忽略的角色
    public static $ignore_role_id = [1];
    public function __construct()
    {
        $this->user_id = session('admin_user_info.id');
        $this->proxy_id = session('admin_user_info.proxy_id');
        $this->role_id = session('admin_user_info.rid');
    }

    public function handle($request, \Closure $next)
    {
//        dump($this->user_id);
//        dump($this->role_id);
//        exit;
        if(!$this->proxy_id && $this->user_id && !in_array($this->role_id,self::$ignore_role_id)){
            $controller = $request->controller();
            $action = $request->action();
            $current_action = strtolower($controller.'/'.$action);
            if(strpos(self::IGNORE_ACTION,$current_action)===false){
                //验证权限问题
                $nodes = $this->_getNode();
                if(strpos($nodes,$current_action)===false){
                    if($request->isAJAX()){
                        return response(['code'=>0,'msg'=>'你无权操作'],200,[],'json');
                    }else{
                        return response('你无权访问');
                    }
                }
            }
        }
        return $next($request);
    }

    //获取当前登录权限节点
    private function _getNode()
    {
        $model = new \app\common\model\Role();
        $node_info = $model->where('id',$this->role_id)->find();
        return $node_info['node'];
    }

}