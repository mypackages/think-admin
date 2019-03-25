<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/7/31
 * Time: 8:45
 */

namespace app\admin\model;


use app\admin\model\jigou\Agency;
use think\Model;

class AdminLoginLog extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $createTime = 'login_time';
    protected $insert = ['ip', 'browser'];


    /**
     * 列表搜索
     */
    public function getList(array $condition, $order = '', $with = '', $isCount = false, $fields = [])
    {
        //搜索创建时间
        if(isset($condition['login_time']))
        {
            $loginTime = array_filter(explode('~', $condition['login_time']));
            unset($condition['login_time']);
            $condition['login_time_start'] = (isset($loginTime[0]) && strtotime(trim($loginTime[0])) !== false) ? strtotime(trim($loginTime[0])) : 0;
            $condition['login_time_end'] = (isset($loginTime[1]) && strtotime(trim($loginTime[1])) !== false) ? strtotime(trim($loginTime[1])) : 0;
        }
        $query = $this;
        if(!empty($fields))
        {
            $query = $query->field($fields);
        }
        if($with != '')
        {
            $query = $query->with($with);
        }

        if(isset($condition['agency_id']) && $condition['agency_id'] > 0)
            $query = $query->where('agency_id', $condition['agency_id']);
        if(isset($condition['owner']) && $condition['owner'] != '')
        {
            if($condition['owner'] == 'system')
            {
                $query = $query->where('agency_id', 0);
            }elseif ($condition['owner'] == 'org') {
                $query = $query->where('agency_id', '>', 0);
            }else{
                $query = $query->where('agency_id', '<', 0);
            }
        }
        if(isset($condition['login_time_start']) && $condition['login_time_start'] > 0)
            $query = $query->where('login_time','>', $condition['login_time_start']);
        if(isset($condition['login_time_end']) && $condition['login_time_end'] > 0)
            $query = $query->where('login_time','<=', $condition['login_time_end']);
        if(isset($condition['username']) && $condition['username'] != '')
        {
            $ids1 = [-1];
            $ids = model('admin/Admin')->field('id')->where("LOCATE('{$condition['username']}', username)")->column('id');
            $query = $query->where('admin_id','in', array_merge($ids1, $ids));
        }
        if(isset($condition['nickname']) && $condition['nickname'] != '')
        {
            $ids1 = [-1];
            $ids = model('admin/Admin')->field('id')->where("LOCATE('{$condition['nickname']}', nickname)")->column('id');
            $query = $query->where('admin_id','in', array_merge($ids1, $ids));
        }
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


    protected function setIpAttr()
    {
        return request()->ip();
    }

    protected function setBrowserAttr()
    {
        return get_client_browser();
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}