var layer;
layui.use(['layer'], function(){
    layer = layui.layer

});
$.common={
    //打开页面
    openUrl(url,title){
        var index = layer.open({
            type:2,
            title:title,
            content:url
        })
        layer.full(index)
    },
    //表单提交
    formSubmit(obj){
        $.post($(obj.form).attr('action'),obj.field,function(result){
                layer.msg(result.msg)
        })
        return false;
    },
    //删除
    del(url, obj){
          layer.confirm('是否删除该数据',function(){
              $.get(url, function(result){
                  layer.msg(result.msg)
                  if(result.code==1){
                      //刷新页面
                      // setTimeout(function(){location.reload()},1000)
                  }
              })
          })
    }
}