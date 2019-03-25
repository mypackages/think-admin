<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/21
 * Time: 13:48
 */

namespace app\admin\validate;


use think\Validate;

class RuleValidate extends Validate
{
    protected $rule =                  [
        'is_menu'  => 'require|in:0,1',
        'pid'   => 'require|checkPid:',
        'name'  =>  'require|unique:auth_rule',
        'title'  =>  'require',
        'icon'  =>  'require',
        'sort'  =>  'require',
        'status'  =>  'require|checkStatus:',
        'id'  =>  'checkId:',
    ];

    protected $message  =   [
        'is_menu.require' => '请选择是否是菜单',
        'is_menu.in'     => '请正确选择是否是菜单',
        'pid.require'   => '请选择父级',
        'pid.checkPid'   => '该父级下不允许增删改查',
        'name.require'   => '请填写规则',
        'name.unique'   => '规则已存在',
        'icon.require'        => '请选择图标',
        'title.require'        => '请填写标题',
        'sort.require'        => '请填写排序权重',
        'status.require'        => '请选择状态',
        'status.checkStatus'        => '状态不正确',
        'id.checkId'    => '该菜单规则不允许修改',
    ];

    protected $scene = [
        'add'  =>  ['is_menu', 'pid', 'name', 'title', 'icon', 'sort', 'status'],
        'edit'  =>  ['is_menu', 'pid', 'name', 'title', 'icon', 'sort', 'status', 'id'],
    ];

    protected function checkPid($value)
    {
        if($value == 5)
            return false;
        return true;
    }
    protected function checkStatus($value)
    {
        if(!in_array($value, ["normal", "hidden"]))
            return false;
        return true;
    }
    protected function checkId($value)
    {
        if(in_array($value, [1, 2, 3, 4, 5, 6]))
            return false;
        return true;
    }
}