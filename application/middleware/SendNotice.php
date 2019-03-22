<?php
namespace app\middleware;

use app\common\service\wechat\Jssdk;

class SendNotice{

    public function handle($request, \Closure $next, $name)
    {
        $date_time = date('Y-m-d H:i:s');
        $handle_info = '';
            $cursor = \think\Db::name('wx_notice')->where('status', 1)->cursor();
            foreach($cursor as $notice){
                try{

                    $link_cond = $notice['link_cond']?json_decode($notice['link_cond'],true):[];
                //发送通知
                if($notice['type']==1){
                    $link_cond['type'] = 'back';
                    //还款通知
                    //创建提示消息--还款通知
                    \app\common\model\UserMessage::recordMsg(1,'还款通知',$notice['content'],$notice['uid'],1,$link_cond);

                }else{
                    //生日通知
                    //创建提示消息
                    \app\common\model\UserMessage::recordMsg(1,'生日通知',$notice['content'],$notice['uid'],0,[]);
                }
                //发送微信通知
                if($notice['openid']){
                    $send_result = \app\common\service\wechat\Jssdk::sendMsg($notice['openid'],$notice['content']);
                    $handle_info = json_encode($send_result);
                }



                }catch (\Exception $e) {
                    $handle_info = $e->getMessage();
                    trace('发送通知处理异常:'.$e->getMessage(),'send_notice_error');
                }

                \think\Db::name('wx_notice')->where(['status'=>1,'id'=>$notice['id']])->update([
                    'status'        => 2,
                    'handle_time'   => $date_time,
                    'handle_info'   => $handle_info,
                ]);
            }


//        exit;
        return $next($request);
    }
}