<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/12/27
 * Time: 11:39
 */

namespace app\admin\traits;


use app\admin\util\SimpleValidator;
use think\Db;

trait ValidateBase
{

    protected function checkUnique($value, $rule, $data)
    {
        $params = explode(',', $rule);
        if(!isset($params[0]) || $params[0] == '' || !isset($params[1]) || $params[1] == '')
        {
            return false;
        }
        $table = $params[0];
        $field = $params[1];
        $isSoftDelete = (!isset($params[2]) || $params[2] == 1) ? 1 : 0;
        $query = Db::name($table)->where($field, $value);
        if($isSoftDelete)
            $query = $query->where('delete_time', 0);
        if(isset($data['id']))
            $query = $query->where('id','<>', $data['id']);
        $row =$query->find();
        if(!empty($row))
        {
            return false;
        }
        return true;
    }


    protected function is_gt_zero($value)
    {
        return $value > 0;
    }

    protected function is_egt_zero($value)
    {
        return $value >= 0;
    }
    protected function isMobile($value)
    {
        if(!SimpleValidator::mobile($value))
        {
            return false;
        }
        return true;
    }
}