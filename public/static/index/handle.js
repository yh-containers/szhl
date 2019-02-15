
$.common={
    //发送验证码
    sendVerify(obj){
        var phone = $(obj).data('phone')
        var type = $(obj).data('type')
        var time=60
        if(!phone) {
            alert('请输入手机号码')
            return false;
        }
        $.get('/index.php/index/sendSms',{phone:phone,type:type},function(result){
            $(obj).attr('disabled',"true")
            alert(result.msg)
            var interval= setInterval(function(){
                if(time>0){
                    $(obj).text('请耐心等待('+time+')')
                }else{
                    clearInterval(interval)
                    $(obj).text('获取验证码')
                    $(obj).removeAttr('disabled')
                }
                --time;
            },1000)
        })

    }

}