<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Date: 2018/6/17
 * Time: 上午7:34
 */

namespace app\admin\util;

class Result
{
    const SUCCESS = 200;
    const ERROR = 500;

    static public function success($msg, $data = [], $isMerge = false, $debug = '', $code = self::SUCCESS)
    {
        $result = [];
        $result['code'] = $code;
        $result['info'] = $msg;
        if($isMerge && is_array($data))
        {
            $result = array_merge($result, $data);
        }else{
            $result['data'] = $data;
        }
        if($debug != '') {
            $result['debug'] = $debug;
        }
        return $result;
    }

    static public function fail($msg, $debug = '', $code = self::ERROR, $data = [])
    {
        $result = [];
        $result['code'] = $code;
        $result['info'] = $msg;
        if($debug != '')
        {
            $result['debug'] = $debug;
        }
        if(!empty($data))
        {
            $result['data'] = $data;
        }
        return $result;
    }

}