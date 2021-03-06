<?php
namespace app\admin\controller;

class Upload extends Common
{
    public function upload($type='images',$open_dir_month=true)
    {
        $upload_file_key=key($_FILES);
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($upload_file_key);
        //上传路径
        $save_path = '/uploads/'.$type.'/';
//        !$open_dir_month && $save_path = $save_path.date('Yhm');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( '.'.$save_path);
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
//            echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
//            echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
//            echo $info->getFilename();
            return [ 'code'=>1,'msg'=>'上传成功','path'=>str_replace('\\','/',$save_path.$info->getSaveName())];
        }else{
            // 上传失败获取错误信息
            return [ 'code'=>0,'msg'=>$file->getError()];
        }

    }

    //富文本上传
    public function editUpload($type='edit')
    {
        $domain = $this->request->domain();
        $upload_file_key=key($_FILES);
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($upload_file_key);
        //上传路径
        $save_path = '/uploads/'.$type.'/';
//        !$open_dir_month && $save_path = $save_path.date('Yhm');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( '.'.$save_path);
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
//            echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
//            echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
//            echo $info->getFilename();
            return [ 'code'=>0,'msg'=>'上传成功','data'=>['src'=>$domain.str_replace('\\','/',$save_path.$info->getSaveName())]];
        }else{
            // 上传失败获取错误信息
            return [ 'code'=>1,'msg'=>$file->getError()];
        }
    }


}
