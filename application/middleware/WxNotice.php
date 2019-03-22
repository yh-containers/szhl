<?php
namespace app\middleware;

class WxNotice{

    //当前日期
    private $date;
    public function handle($request, \Closure $next)
    {
        $model = new \app\common\model\WxNotice();
        $this->date = date("Y-m-d");

        //获取用户生日消息提醒
        $birthday_notice = $this->getUserBirthday();
//        trace('通知数据'.json_encode($birthday_notice),'record_notice_data');
        $plan_notice = $this->getBackPlan();
//        trace('通知数据'.json_encode($plan_notice),'record_notice_data');
        //处理通知数据
        $notice_data = array_merge($birthday_notice,$plan_notice);
//        trace('通知数据'.json_encode($notice_data),'record_notice_data');
        if($notice_data){
            try{
                $model->startTrans();
                $model->saveAll($notice_data);
                $this->getUserBirthday(true);
                $this->getBackPlan(true);
                $model->commit();
            }catch (\Exception $e) {
                trace('通知数据'.$e->getMessage(),'record_notice_logs');
                $model->rollback();
            }

        }
        return $next($request);
    }

    //获取用户生日消息提醒
    private function getUserBirthday($is_close=false)
    {
        $model = new \app\common\model\Users();
        //获取今天是用户生日的用户
        $model = $model->where([
            ['birthday','like','%'.substr($this->date,5)],
            ['openid','<>',''],
        ])->whereRaw("birthday_notice_state<>:date or birthday_notice_state is null", ['date' => $this->date]);
        if(!$is_close){
            //获取数据
            $record_data = [];
            $model->select()->each(function($item,$index)use(&$record_data){
                $record_data[] = [
                    'type'          =>  0,
                    'uid'           =>  $item['id'],
                    'openid'        =>  $item['openid'],
                    'cond_date'     =>  $this->date,
                    'content'       =>  '今天是您的生日，祝你生活愉快',
                ];
            });
            return $record_data;

        }else{
            //关闭数据
            $model->setField('birthday_notice_state',$this->date);

        }


    }

    //获取还款计划消息提醒
    private function getBackPlan($is_close=false)
    {
        $record_data = [];
        $model = new \app\common\model\UserProductPlan();
        //获取今天是用户生日的用户
        $model=$model->where([
            ['date','=',$this->date],
            ['is_notice','=',0],
        ]);

        if(!$is_close){
            $model=$model->withJoin('linkUserInfo','left')->where([
                ['openid','<>',''],
            ]);
            $model->select()->each(function($item,$index)use(&$record_data){
                $record_data[] = [
                    'type'          =>  1,
                    'uid'           =>  $item['link_user_info']['id'],
                    'openid'        =>  $item['link_user_info']['openid'],
                    'cond_date'     =>  $this->date,
                    'content'       =>  '今天是您的还款日',
                    'link_cond'     =>  ['id'=>$item['id'],'req_id'=>$item['req_id']],
                ];
            });
            return $record_data;

        }else{
            //关闭数据
            $model->setField('is_notice',1);
        }

    }
}

