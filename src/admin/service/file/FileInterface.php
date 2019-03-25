<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Date: 2019/3/24
 * Time: 下午12:53
 */

namespace app\admin\service\file;


interface FileInterface
{
    public function upload($file, $type);

    public function delete($fileInfo);

}