<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Date: 2019/3/24
 * Time: 下午12:49
 */

namespace app\admin\service\file\driver;


use app\admin\service\file\FileInterface;
use think\Exception;
use think\facade\Env;

class Local implements FileInterface
{
    protected $uploadDir = 'uploads';
    protected $fileDomain = '';
    protected $baseDir;
    public function __construct()
    {
        $this->baseDir = Env::get('root_path').'/public';
    }

    public function upload($file, $type)
    {
        $dir = '/'.$this->uploadDir.'/'.$type;
        $info = $file->move($this->baseDir.$dir);
        if(!$info)
            throw new Exception($file->getError());
        $path = $dir.'/'.$info->getSaveName();
        $url = $this->fileDomain.$path;
        return [$path, $url];
    }


    public function delete($fileInfo)
    {
        $realPath = $this->baseDir.$fileInfo['path'];
        if(is_file($realPath))
        {
            unlink($realPath);
        }
    }
}