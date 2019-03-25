<?php
namespace app\admin\controller;

use app\admin\traits\AjaxReturn;
use app\admin\util\StringToolkit;
use think\Db;
use think\Exception;

class File extends Base
{

    use AjaxReturn;

    public function initialize()
    {
        $this->noAuthAction = ['getFiles','getUUID'];
        return parent::initialize();
    }



    /**
     * date: 2018-07-20
     * 文件上传
     * @author liupan
     */
    public function upload()
    {
        try {
            $uploadName = input('param.upload_name', 'file');
            $type = input('param.type', '');
            $uuid = input('uuid', null);
            $file = request()->file($uploadName);
            if (empty($file))
                throw new Exception('未上传文件');
            if($type == '')
                throw new Exception("缺少参数type");
            $path = app('file')->upload($file, $type, $uuid);
            $this->successReturn('上传成功', ['imgUrl' => $path]);
        } catch (Exception $e) {
            $this->failReturn($e->getMessage());
        }
    }



    public function deleteFile()
    {
        try{
            $id = input('id', 0, 'intval');
            if($id <= 0)
                throw new Exception('缺少参数id');
            app('file')->delete($id);
            $this->successReturn('删除成功');
        }catch (Exception $e)
        {
            $this->failReturn($e->getMessage());
        }
    }





    public function getFiles()
    {
        try{
            $type = input('param.type', '');
            $uuid = input('param.uuid', '');
            if($type == '')
                throw new Exception("缺少参数type");
            if($uuid == '')
                throw new Exception("缺少参数uuid");

            $list = Db::name('file')->where('type', $type)->where('uuid', $uuid)->select();;
            foreach ($list as $k => &$v)
            {
                $v['delete_url'] = url('file/deleteFile', ['id' => $v['id']]);
            }
            $this->successReturn('查询成功', $list);
        }catch (Exception $e)
        {
            $this->failReturn($e->getMessage());
        }
    }



    public function getUUID()
    {
        $this->successReturn('请求成功',StringToolkit::keyGen());
    }



}
