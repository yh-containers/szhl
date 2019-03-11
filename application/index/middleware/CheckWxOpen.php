<?php
namespace app\index\middleware;

class CheckWxOpen{

    public function handle($request, \Closure $next, $name)
    {

        if (preg_match('~micromessenger~i', $request->header('user-agent'))) {
            //微信浏览器
            $is_check_wechat = cookie('is_check_wechat');
            if(!$is_check_wechat && !session('?user_info') && !session('auth_wx_openid')){
                $code = $request->get('code');
                if($code){
                    //获取网页access_token
                    $result = \app\common\service\wechat\Jssdk::getWebAccessToken($code);
                    if($result!==false){
                        //认证成功---缓存用户授权auth_openid
                        session('auth_info_wx',$result);
                        $req_info = (new \app\common\model\UserWechatSubscribe())->where('openid',$result['openid'])->order('id','desc')->find();
                        if(!empty($req_info) && !empty($req_info['req_user_id'])){
                            //保存session信息
                            session('req_user_id',$req_info['req_user_id']);
                        }
                    }
                    //记录已验证wechat
                    cookie('is_check_wechat',1);
                }else{
                    //进行微信授权登录
                    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.\app\common\service\wechat\Config::APPID.'&redirect_uri='.urlencode($request->url(true)).'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
                    return redirect($url);

                }

            }


        } elseif (preg_match('~alipay~i', $request->header('user-agent'))) {

        }

        return $next($request);
    }
}