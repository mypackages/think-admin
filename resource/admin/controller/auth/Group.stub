<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/22
 * Time: 11:05
 */

namespace app\admin\controller\auth;


use app\admin\controller\Base;
use app\admin\validate\GroupValidate;
use think\Exception;

class Group extends Base
{
    protected $noAuthAction = ['groupApi'];
    protected $indexArraySort = true;
    public function initialize()
    {
        parent::initialize();
        $this->model = model('admin/AuthGroup');
        $this->validateRule = new GroupValidate();
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
                throw new Exception("id为{$v}的用户组不允许删除");
            }
        }
        return true;
    }

    public function groupApi()
    {
        $condition = input('param.');
        $list = $this->model->getList($condition)->toArray();
        return $this->successReturn('请求成功', array_sort($list, 0));
    }
}