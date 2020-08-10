<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/22
 * Time: 11:07
 */

namespace app\admin\validate;


use think\Validate;

class GroupValidate extends Validate
{
    protected $rule = [
        'id'  =>  'require|checkId:',
        'pid'   => 'require',
        'name'  =>  'require',
        'rules'  =>  'require',
        'status'  =>  'require',
    ];
    protected $message  =   [
        'id.require'   => 'id不正确',
        'id.checkId'   => '该用户组不允许修改',
        'pid.require'   => '请选择父级',
        'name.require'   => '请填写组名',
        'rules'        => '请选择至少一个权限',
        'status.require'        => '请选择状态',
    ];

    protected $scene = [
        'add'  =>  ['pid', 'name', 'rules', 'status'],
        'edit'  =>  ['id', 'pid', 'name', 'rules', 'status'],
    ];


    protected function checkId($value)
    {
        if($value == 1)
            return false;
        return true;
    }

}