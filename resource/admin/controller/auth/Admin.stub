<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/25
 * Time: 8:14
 */

namespace app\admin\controller\auth;


use app\admin\controller\Base;
use app\admin\validate\AdminValidate;
use app\admin\util\StringToolkit;
use think\Exception;

class Admin extends Base
{
    protected $noAuthAction = [];
    protected $indexOrder = 'id desc';
    protected $indexWith = ['groupAccess.group'];
    protected $editWith = ['groupAccess.group'];
    protected $indexHasPage = true; //index方法的ajax请求是否需要分页
    public function initialize()
    {
        parent::initialize();
        $this->model = model('admin/Admin');
        $this->validateRule = new AdminValidate();
    }

    /**
     * 新增
     */
    protected function addHandle($data)
    {
        $data['uuid'] = StringToolkit::keyGen();
        $data['salt'] = StringToolkit::randString(6);
        $data['password'] = $this->model->encryptPassword($data['password'], $data['salt']);
        $groupAccess = model('admin/AuthGroupAccess');
        $groupAccess->group_id = $data['group_id'];
        foreach ($data as $k => $v)
        {
            $this->model->$k = $v;
        }

        $this->model->groupAccess = $groupAccess;
        return $this->model->together('groupAccess')->allowField(true)->save();
    }
    /**
     * 修改
     */
    protected function editHandle($data, $id)
    {
        if(isset($data['password']))
        {
            if($data['password'] == '')
            {
                unset($data['password']);
            }else{
                $data['salt'] = StringToolkit::randString(6);
                $data['password'] = $this->model->encryptPassword($data['password'], $data['salt']);
            }
        }
        // 查询
        $row = $this->model->get($id);
        if(empty($row))
        {
            return false;
        }
        foreach ($data as $k => $v)
        {
            $row->$k = $v;
        }
        $row->groupAccess->group_id = $data['group_id'];
        return $row->together('groupAccess')->allowField(true)->save();
    }


    /**
     * 删除前验证
     */
    protected function deleteValidate($ids)
    {
        foreach ($ids as $v)
        {
            if($v == 1)
            {
                throw new Exception("id为{$v}的管理员不允许删除");
            }
        }
        return true;
    }
}