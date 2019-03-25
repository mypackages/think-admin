<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2019/3/23
 * Time: 15:14
 */

namespace app\admin\traits;


trait ModelBase
{
    /**
     * 列表搜索
     */
    public function getList(array $condition, $order = '', $with = '', $isCount = false, $fields = '')
    {
        $query = $this->buildCondition($condition, $with, $fields);
        if($isCount)
        {
            return $query->count();
        }
        if(isset($condition['page']) && isset($condition['limit']) && $condition['page'] > 0 && $condition['limit'] > 0)
        {
            $query = $query->page($condition['page'], $condition['limit']);
        }
        if($order == '')
        {
            $order = 'id desc';
        }
        $query = $query->order($order);
        if(!empty($this->hidden))
        {
            $query = $query->hidden($this->hidden);
        }
        if(!empty($this->visible))
        {
            $query = $query->visible($this->visible);
        }
        return $query->select();
    }

    /**
     * 创建搜索条件
     */
    protected function buildListCondition($condition, $with, $fields)
    {
        return $this;
    }
}