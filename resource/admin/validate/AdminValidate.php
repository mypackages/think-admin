<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/25
 * Time: 8:17
 */

namespace app\admin\validate;


use app\admin\traits\ValidateBase;
use app\admin\util\SimpleValidator;
use think\Validate;

class AdminValidate extends Validate
{
    use ValidateBase;
    protected $rule = [
        'id'  =>  'require|checkId:',
        'group_id'   => 'require|is_gt_zero:',
        'username'  =>  'require|checkUsername:|unique:admin',
        'email'  =>  'existIsEmail',
        'nickname'  =>  'require',
        'password' => 'require|checkPassword:',
        'mobile'  =>  'isMobile:',
        'status'  =>  'checkStatus:',
    ];
    protected $message  =   [
        'id.require'   => 'id不正确',
        'id.checkId'   => '该管理员不允许修改',
        'group_id.require'   => '请选择用户组',
        'group_id.is_gt_zero'   => '请选择用户组',
        'username.require'   => '请填写用户名',
        'username.unique'   => '用户名已存在',
        'username.checkUsername' => '用户名必须为4-20位字母开头的字母数字组合',
        'email.existIsEmail'   => '邮箱格式不正确',
        'nickname.require'   => '请填写昵称',
        'password.require'   => '请输入密码',
        'password.checkPassword'        => '密码必须为6-20位的非空字符',
        'mobile.isMobile' => '手机号码不正确',
        'status.checkStatus'        => '请选择正确的状态',
    ];

    // add 验证场景定义
    public function sceneAdd()
    {
        return $this->only(['group_id', 'username', 'email', 'password', 'status', 'nickname', 'mobile']);
    }
    // edit 验证场景定义
    public function sceneEdit()
    {
        return $this->only(['id', 'group_id', 'username', 'email', 'password', 'status', 'nickname', 'mobile'])
            ->remove('password', 'require');
    }

    // profile 验证场景定义,用于系统后台和机构后台管理员修改自己资料
    public function sceneProfile()
    {
        return $this->only(['email', 'password', 'nickname', 'mobile', 'qq'])
            ->remove('password', 'require');
    }

    protected function checkId($value)
    {
        if($value == 1)
            return false;
        return true;
    }




    protected function checkUsername($value)
    {
        if(!SimpleValidator::username($value))
        {
            return false;
        }
        return true;
    }


    protected function existIsEmail($value)
    {
        if($value == '')
        {
            return true;
        }
        if(!SimpleValidator::email($value))
        {
            return false;
        }
        return true;
    }

    protected function checkPassword($value)
    {
        if($value == '')
        {
            return true;
        }
        if(!SimpleValidator::password($value))
        {
            return false;
        }
        return true;
    }

    protected function checkStatus($value)
    {
        if(!in_array($value, ["normal", "locked"]))
            return false;
        return true;
    }


}