<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/19
 * Time: 16:59
 */

namespace app\admin\model;

use app\admin\event\Logging;
use app\admin\traits\ModelBase;
use think\Model;
use think\model\concern\SoftDelete;

class AuthRule extends Model
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

    //定义获取器
    public function getStatusTextAttr($value, $data)
    {
        $status = ['normal' => '显示', 'hidden'=> '隐藏'];
        if(isset($status[$data['status']]))
            return $status[$data['status']];
        return '';
    }


    /**
     * 创建列表搜索条件
     */
    protected function buildCondition($condition, $with, $fields)
    {
        $query = $this;
        if($with != '')
            $query = $query->with($with);
        if($fields != '')
            $query = $query->field($fields);
        if(isset($condition['status']) && $condition['status'] != '')
            $query = $query->where('status', $condition['status']);
        if(isset($condition['title']) && $condition['title'] != '')
            $query = $query->where('title','like', '%'.$condition['title'].'%');
        if(isset($condition['is_menu']) && $condition['is_menu'] != '')
            $query = $query->where('is_menu', $condition['is_menu']);
        return $query;
    }

    /**
     * 获取菜单
     */
    public function getMenu($showHidden = 1)
    {
        $query = $this->where('is_menu', 1);
        if(!$showHidden)
        {
            $query = $query->where('status', 'normal');
        }
        return $query->order('sort desc')->select();
    }
}