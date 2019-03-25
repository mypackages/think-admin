<?php
/**
 * Created by PhpStorm.
 * User: liupan
 * Email: 498501258@qq.com
 * Date: 2018/6/20
 * Time: 8:51
 */

namespace app\admin\controller\auth;


use app\admin\controller\Base;
use app\admin\validate\RuleValidate;
use app\admin\util\ArrayToolkit;
use think\Db;
use think\Exception;

class Rule extends Base
{
    protected $noAuthAction = ['getMenu', 'getTree'];
    protected $indexArraySort = true;
    protected $indexOrder = 'sort desc';
    public function initialize()
    {
        parent::initialize();
        $this->model = model('admin/AuthRule');
        $this->validateRule = new RuleValidate();
    }


    protected function addHandle($data)
    {
        $rzt = $this->model->allowField(true)->save($data);
        if($rzt)
        {
            //更新父级的子级
            $allRules = model('admin/AuthRule')->getList([], 'sort desc')->toArray(); //不能用缓存要实时查询
            $allRules = ArrayToolkit::index($allRules, 'id');
            $parents = find_rule_parents($this->model->id, $allRules);
            foreach ($parents as $pid)
            {
                $childIds = implode(',', get_child_ids($allRules, $pid));
                model('admin/AuthRule')->where('id', $pid)->update(['child_ids' => $childIds]);
            }
        }
        return $rzt;
    }



    protected function editHandle($param, $id)
    {
        //更新的时候把当前菜单规则的子级写进去
        $allRules = model('admin/AuthRule')->getList([], 'sort desc')->toArray();
        $allRules = ArrayToolkit::index($allRules, 'id');
        $childIds = implode(',', get_child_ids($allRules, $id));
        $param['child_ids'] = $childIds;
        $rzt = $this->model->allowField(true)->save($param, ['id' => $id]);
        //更新父级的子级
        $parents = find_rule_parents($id, $allRules);
        foreach ($parents as $pid)
        {
            $childIds = implode(',', get_child_ids($allRules, $pid));
            model('admin/AuthRule')->where('id', $pid)->update(['child_ids' => $childIds]);
        }
        return $rzt;
    }





    public function getTree()
    {
        $condition = input('param.');
        $list = $this->model->getList($condition, 'sort desc')->each(function ($row){
            $row['pId'] = $row['pid'];
            $row['name'] = $row['title'];
            $row['open'] = true;
            unset($row['icon']);
        });
        return $this->successReturn('请求成功', array_sort($list, 0));
    }


    //获取菜单的api
    public function getMenu($isSort = 1, $showHidden = 1)
    {
        $menu = $this->model->getMenu($showHidden);
        if($isSort)
        {
            $menu = array_sort($menu, 0);
        }
        return $this->successReturn('请求成功', $menu);
    }

    /**
     * 删除前验证
     */
    protected function deleteValidate($ids)
    {
        foreach ($ids as $v)
        {
            if(in_array($v, [1, 2, 3, 4, 5, 6]))
            {
                throw new Exception("id为{$v}的菜单规则不允许删除");
            }
        }
        return true;
    }
}