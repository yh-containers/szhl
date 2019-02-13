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
    },
    //修改
    modify(url, obj,title){
          title = title?title:'是否修改数据';
          obj = Object.assign({},obj)
          layer.confirm(title,function(){
              $.post(url,obj, function(result){
                  layer.msg(result.msg)
                  if(result.code==1){
                      //刷新页面
                      // setTimeout(function(){location.reload()},1000)
                  }
              })
          })
    },
    //文件上传
    uploadFile(upload,elem,func){
        //执行实例
        var uploadInst = upload.render({
            elem: elem //绑定元素
            ,acceptMime:'image/*'
            ,done: function(res, index, upload){
                //获取当前触发上传的元素，一般用于 elem 绑定 class 的情况，注意：此乃 layui 2.1.0 新增
                var item = this.item;
                if(func){

                }else{
                    $(item).parent().find('img').attr('src',res.path)
                    $(item).prev().val(res.path)
                }
            }
            ,error: function(){
                //请求异常回调
                layer.msg('上传异常')
            }
        });
    }


}