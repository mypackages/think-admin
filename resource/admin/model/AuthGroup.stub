<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/19
 * Time: 12:23
 */

namespace app\admin\model;


use app\admin\traits\ModelBase;
use think\Model;
use think\model\concern\SoftDelete;

class AuthGroup extends Model
{
    // 引入软删除
    use SoftDelete;
    use ModelBase;
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
    /**
     * 创建列表搜索条件
     */
    protected function buildCondition($condition, $with, $fields)
    {
        $query = $this;
        if($with != '')
        {
            $query = $this->with($with);
        }
        if($fields != '')
            $query = $query->field($fields);
        if(isset($condition['status']) && $condition['status'] != '')
            $query = $query->where('status', $condition['status']);
        if(isset($condition['name']) && $condition['name'] != '')
            $query = $query->where('name','like', '%'.$condition['name'].'%');
        return $query;
    }
}