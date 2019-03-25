<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Date: 2018/6/17
 * Time: 上午8:08
 */

namespace app\admin\traits;


use app\admin\util\Result;

trait AjaxReturn
{
    public function successReturn($msg = '操作成功', $data = [], $isMerge = false, $debug = '', $code = 200)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode(Result::success($msg, $data, $isMerge, $debug, $code)));
    }

    public function failReturn($msg = '操作失败', $debug = '', $code = 500, $data = [])
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode(Result::fail($msg, $debug, $code, $data)));
    }

}